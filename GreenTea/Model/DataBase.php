<?php
/**
 * @name DataBase.php
 * @author 宋文峰(songwenfeng@baidu.com)
 * @date 13-7-20
 * @version 0.02
 * @desc model基类
 * @comment 关于关联查询：
 *  1toN：先查1，然后把1的key加入条件中查N，最后把1夹到N的结果中。
 *  Nto1，先查N，然后把N的foreign_key加入条件中查1，最后把1夹到N的结果中。
 *  NtoM，先查N，把N的foreign_key当条件查关联表，再把关联表的M的key加入条件查询M，最后把M和N合并到关联表上。
 */
namespace GreenTea\Model;

use GreenTea\DI\Services;
use GreenTea\DI\Web as DI;
use GreenTea\Model\DataBase\Strategy;
use GreenTea\Model\DataBase\Transaction;
use GreenTea\Model\Sql\Factory;
use GreenTea\Utility\XArray;

abstract class DataBase extends Strategy{
    const COLUMNS_FIELD = 'Field';
    const COLUMNS_TYPE = 'Type';
    const COLUMNS_NULL = 'Null';
    const COLUMNS_KEY = 'Key';
    const COLUMNS_DEFAULT = 'Default';
    const COLUMNS_DEFAULT_TIMESTAMP = 'CURRENT_TIMESTAMP';
    const COLUMNS_DEFAULT_NULL = 'NULL';
    const COLUMNS_EXTRA = 'Extra';
    const PRIMARY_KEY = 'PRI';

    /**
     * @var \Phalcon\Logger\Adapter
     */
    protected $_logger;

    /**
     * @var string
     * 留空则使用db配置中的连接时的数据库
     */
    protected $_db_name = '';
    protected $_table_name = '';

    /**
     * @var array   格式array('fields1', 'fields2')
     * todo 准备用fields替代
     */
    protected $_primary_key = [];
    /**
     * @var array
     * 表字段信息，字段的类型、主键、默认值等
     */
    protected $_columns = [];

    /**
     * @var \GreenTea\Model\DataBase\Transaction
     * 事务控制
     */
    private $_transaction = null;

    protected $_last_statement;

    /**
     * 构造函数
     */
    public function __construct(){
        if(!$this->_table_name){
            throw new \Exception('Table Name[_table_name] must be set!');
        }
        parent::__construct(Services::CONFIG_DB);
        $this->_logger = DI::getDefault()->getShared(Services::SERVICE_LOGGER);
    }

    /**
     * @param $result
     * @param string $error_message 错误信息
     * @throws \Exception
     */
    protected function _exception($result, $error_message){
        if($result === false){
            $this->_logger->error('[SQL Error]:' . $error_message);
            throw new \Exception($error_message);
        }
    }

    /**
     * 返回执行sql时的真实表名。分表时，子类就需要覆写此方法。
     * 动态分表也需要在子类中通过查全局(Level 1)表来确定具体的表名
     * @param Array $fields_or_condition
     *  分表时，传空数组时应该返回默认的某个有效表名
     * @return string
     */
    public function getTableName(Array &$fields_or_condition = []){
        return $this->_table_name;
    }

    /**
     * 无论哪种分表，数据库名都必须相同。
     */
    public function getDbName(){
        return $this->_db_name;
    }

    /**
     * 对传入数组根据当前表的字段进行过滤，如果数组中包含该表不存在的字段则去除
     * 用于insert/update/delete的fields和condition过滤
     * @param array $fields_or_condition
     * @return void
     * @comment condition过滤主要是用于一个condition查找多个表的情况。
     */
    protected function _filterFields(Array &$fields_or_condition){
        foreach ($fields_or_condition as $key => &$value) { //同时把值为NULL的过滤掉
            if($key === Factory::_OR_ || $key === Factory::_AND_){ //如果是or、and关系符号则进入下一层
                $this->_filterFields($value);
                continue;
            }
            if(!isset($this->_getTableColumns()[$key]) || $value === NULL){
                unset($fields_or_condition[$key]);
                $this->_logger->notice('There is no such field in this table['.$key.']');
            }
        }
    }

    /**
     * 获取主键的键名
     * @return array
     */
    protected function _getPkFields(){
        $pk = [];
        foreach ($this->_getTableColumns() as $k => $desc) {
            if($desc[self::COLUMNS_KEY] == self::PRIMARY_KEY) $pk[] = $k;
        }
        return $pk;
    }

    /**
     * 根据condition，判断是否为主键查找，如果是则返回主键值，否则返回false.
     * @param array $condition
     * @return bool|string
     */
    protected function _getPkValue(Array $condition){
        $pkf = $this->_getPkFields();
        $pkv = '';
        foreach ($pkf as $key) {
            if(isset($condition[$key])) {
                $pkv .= $condition[$key];
            }else return false;
        }
        return $pkv;
    }

    /**
     * 获取表结构信息
     * @return array
     */
    protected function _getTableColumns(){
        $this->_initTableColumns();
        return $this->_columns;
    }

    /**
     * 初始化表结构信息，如果设置了$this->_columns则使用设置的
     * 如果没有设置$this->_columns，或是只设了'*'则从数据库中获取
     * @param string $table
     * @return array
     */
    protected function _initTableColumns($table = ''){
        if(!$this->_columns || !is_array($this->_columns) || current($this->_columns) == '*'){
            if(!$table){
                $table = $this->getTableName(); //是否分表都会返回一个有效表名
            }
            $fields = [];
            $sql = 'SHOW COLUMNS FROM `' . $table . '`';
            $driver = $this->_getDriver(
                $this->_assembleFactor($table, Distribute::OP_READ));

            $columns = $driver->query($sql);
            $this->_exception($columns, "No such TABLE[$table] in the the Database!");

            foreach ($columns as $v) {
                $fields[$v[self::COLUMNS_FIELD]] = $v;
            }
            $this->_columns = $fields;
        }
    }

    /**
     * 如果condition为非数组，则认为其是主键值
     * @param $condition
     * @throws \Exception
     * @return array
     */
    protected function _assemblePkCondition($condition){
        if(is_array($condition)){
            return $condition;
        }elseif(!$condition){ //
            return [];
        }
        $pkf = $this->_getPkFields();
        //如果复合主键，则取第一个
        return [current($pkf) => $condition];

    }

    /**
     * 一个表只能部署在一个节点，所以只需table_name即可确认部署节点
     * {@inheritdoc}
     */
    protected function _assembleFactor($table, $op_type){
        return parent::_assembleFactors($this->getDbName(), $table, $op_type);
    }

    /**
     * @param $factor
     * @return \PhalconEx\Db\Adapter\Mysql
     */
    protected function _getDriver(Array $factor){
        if($this->_transaction){
            return $this->_transaction->getDriver();
        }
        return parent::_getDriver($factor);
    }


    protected function _query($curd, $table, Array $fields = [], Array $condition = [],
                              Array $append = [], $error_message = ''){
        $this->_initTableColumns($table);
        $op_type = Distribute::OP_WRITE;
        $func = 'execute';
        if($curd === Factory::SELECT) {
            $op_type = Distribute::OP_READ;
            $func = 'query';
            if(empty($fields)) { //如果fields为空则使用子类设置的fields或表的meta_fields
                $fields = $this->_getTableColumns();
            }
        }else{
            $this->_filterFields($fields);
        }
        $this->_filterFields($condition);

        //$table = $this->_getTableName($condition);
        $statement = Factory::getInstance()->getShared($curd)
            ->assemble($table, $fields, $condition, $append);
        $driver = $this->_getDriver($this->_assembleFactor($table, $op_type));
        $this->_driver = $driver; //temporary store the connection for some needing.
        $this->_last_statement = $statement;
        try{
            $result = $driver->$func($statement);
        }catch (\Exception $e){
            $result = false;
            $error_message .= ' ' . $e->getMessage();
        }
        $this->_exception($result, "[$error_message] SQL: $statement");
        return $result;
    }

    public function insertOrUpdate(Array $fields){
        $table = $this->getTableName($fields);
        $this->_query(Factory::INSERT_OR_UPDATE, $table, $fields, [], [],
            'INSERT AND ON DUPLATE UPDATE Failed!');
        return $this->_driver->lastInsertId();
    }

    public function insert(Array $fields){
        $table = $this->getTableName($fields);
        $this->_query(Factory::INSERT, $table, $fields, [], [], 'INSERT Failed!');
        return $this->_driver->lastInsertId();
    }

    /**
     * @param array $condition
     * @return bool
     */
    public function delete(Array $condition){
        //$condition = $this->_primaryKeyCondition($condition);
        $table = $this->getTableName($condition);
        $result = $this->_query(Factory::DELETE, $table, [], $condition, [], 'DELETE Failed!');
        return $result;
    }

    public function update(Array $fields, Array $condition){
        $table = $this->getTableName($condition);
        $result = $this->_query(Factory::UPDATE, $table, $fields, $condition, [], 'UPDATE Failed!');
        return $result;
    }

    /**
     * @param array $condition 无法支持只传主键值的行为，因为如果分表，主键无法确认DB位置和表名。
     * @param array $append
     * @return array
     */
    public function getOne(Array $condition, Array $append = []){
        $append[Factory::LIMIT] = "1";
        $ret = $this->getList($condition, $append);
        if(!empty($ret)){
            return current($ret);
        }else{
            return $ret;
        }
    }

    /**
     *
     * @param Array $condition
     * @param array $append
     * @internal param string $order_by
     * @internal param string $limit
     * @internal param string $return_key 就是如果想让返回结果以某个字段为索引，则指定。
     *          暂只支持一个字段，注意：如果重复则会覆盖前面记录，
     * @param array $fields
     * @return array
     */
    public function getList(Array $condition = [], Array $append = [], Array $fields = []){
        $return_key = XArray::fetchItem($append, Factory::RETURN_KEY);
        $result = $this->select($condition, $append, $fields);
        if($return_key){
            $tmp_ret = [];
            foreach ($result as $v) {
                $tmp_ret[$v[$return_key]] = $v;
            }
            $result = $tmp_ret;
        }
        return $result;
    }

    /**
     * @param array $condition
     * @param array $append
     * @param array $fields
     * @return mixed
     */
    public function select(Array $condition = [], Array $append = [], Array $fields = []){
        $table = $this->getTableName($condition);
        //去除传入fields的空和null字段，
        XArray::trim($fields);

        $result = $this->_query(Factory::SELECT, $table, $fields, $condition, $append, 'SELECT Failed!');
        return $result;
    }

    /**
     * @param array $condition
     * @param string $group_by
     * @param array $fields
     * @return mixed
     */
    public function getGroup(Array $condition = [], $group_by = '', Array $fields = []){
        $arr_group_by = explode(',', $group_by);
        if(empty($fields)) {
            //这里，groupby之后，其他字段都是只取第一条所以没什么意义。只有count之类有意义
            $fields = $arr_group_by;
        }else{
            $fields = array_merge($fields, $arr_group_by);
        }
        $append[Factory::GROUP_BY] = $group_by;
        return $this->select($condition, $append, $fields);
    }

    /**
     * @param array $condition
     * @return mixed
     */
    public function getCount(Array $condition = []){
        $table = $this->getTableName($condition);
        $result = $this->_query(Factory::SELECT, $table,
            ['count(*) as num'], $condition, null, 'COUNT Failed!');
        return current($result)['num'];
    }

    /**
     * 获取一条记录的某个字段
     * @param $condition
     * @param $column
     * @return mixed
     */
    public function getColumn(Array $condition, $column){
        $row = $this->getOne($condition);
        if($row){
            return $row[$column];
        }else{
            return false;
        }
    }

    /**
     * 获取某一列的数组
     * @param $conds
     * @param $column
     * @return array
     */
    public function getColumnList(Array $conds, $column){
        $tmp = $this->getList($conds);
        $result = [];
        foreach ($tmp as $v) {
            $result[$v[$column]] = $v[$column];
        }
        return $result;
    }

    public function getLastSqlStatement(){
        return $this->_last_statement;
    }

    public function startTransaction(Transaction $transaction){
        if($this->_transaction){
            throw new \Exception('This Model[' . $this->_table_name . '] Already in another Transaction!');
        }
        $this->_transaction = $transaction;
    }

    public function endTransaction(){
        $this->_transaction = null;
    }

    /**
     * TODO: 通过__call()实现动态方法getFirstBy<Column>()，参考phalcon/model的findFirstBy<Column>()
     */
}