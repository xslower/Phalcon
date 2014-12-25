<?php
/**
 * @name Correlate.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/12/22 0022
 * @version 0.01
 */

namespace GreenTea\Model\DataBase;

use GreenTea\Model\DataBase;
use GreenTea\Model\Sql\Factory;

class Correlate extends DataBase{
    const HAS_MANY      = 'HAS_MANY';
    const BELONGS_TO    = 'BELONGS_TO';
    const MANY2MANY     = 'MANY_TO_MANY';
    const RELATE_TYPE   = 'CORRELATE_TYPE';
    const RELATE_TABLE  = 'CORRELATE_TABLE';
    const RELATE_TABLE_KEY = 'CORRELATE_KEY';
    const ASSOC_TABLE   = 'ASSOCIATIVE_TABLE';
    const ASSOC_TABLE_SELF_KEY = 'SELF_KEY';
    const ASSOC_TABLE_RELATE_KEY = 'RELATE_KEY';

    /**
     * 外键定义，用于自动关联表查询。示例：
     * 'feedback' => array(
     *  self::RELATE_TYPE => self::BELONGS_TO,  //关联类型
     *  self::RELATE_TABLE => 'Users',   //关联表的model
     *  self::RELATE_TABLE_KEY => 'id',  //关联表的主键key
     * ).
     * @var array
     * @alert 如果一个表有多个外键，则关联类型必须为belongs to，否则无法进行关联表数据组合。
     */
    protected $_foreign_key;

    /**
     * @var bool    给correlateReset用。记住子类设置的默认值
     */
    protected $_correlate_default;
    protected $_use_correlate = false;

    public function __construct(){
        parent::__construct();

        $this->_correlate_default = $this->_use_correlate;
    }

    public function getList(Array $condition = [], Array $append = [], Array $fields = []){
        $list = parent::getList($condition, $append, $fields);
        if($this->_use_correlate){
            $list = $this->_getRrelate($list, $condition);
        }
        return $list;
    }


    /**
     * 关联查询，把所有外键均转化成关联表对应的记录
     * @param array $list   查询结果列表
     * @param array $condition  主要是给HAS_MANY和MANY2MANY用的，用于对其多条数据的筛选
     * @return array
     * @throws \Exception
     */
    protected function _getRrelate(Array $list, Array $condition){
        if(!$this->_foreign_key || !is_array($this->_foreign_key)){
            return $list;
        }
        if(count($this->_foreign_key) > 1){
            foreach ($this->_foreign_key as $fkey => $config) {
                if($config[self::RELATE_TYPE] != self::BELONGS_TO){
                    throw new \Exception('Foreign Key Setting Error! Multiple foreign key must be [BELONGS_TO]!');
                }
                $relate_table_key = $config[self::RELATE_TABLE_KEY];
                $one_list = $this->_getRelateData($list, $fkey,
                    $config[self::RELATE_TABLE], $condition, $relate_table_key);
                $this->_assembleRelateData($list, $fkey, $one_list, $relate_table_key);
            }
            return $list;
        }
        $foreign_key = each($this->_foreign_key);
        $fkey = $foreign_key['key'];
        $config = $foreign_key['value'];
        $relate_table_key = $config[self::RELATE_TABLE_KEY];

        switch($config[self::RELATE_TYPE]){
            case self::BELONGS_TO:
                $one_list = $this->_getRelateData($list, $fkey,
                    $config[self::RELATE_TABLE], $condition, $relate_table_key);
                $this->_assembleRelateData($list, $fkey, $one_list, $relate_table_key);
                return $list;
            case self::HAS_MANY:
                $many_list = $this->_getRelateData($list, $fkey,
                    $config[self::RELATE_TABLE], $condition, $relate_table_key);
                $this->_assembleRelateData($many_list, $relate_table_key, $list, $fkey);
                return $many_list;
            case self::MANY2MANY:
                $at_self_key = $config[self::ASSOC_TABLE_SELF_KEY];
                $at_relate_key = $config[self::ASSOC_TABLE_RELATE_KEY];
                $assoc_list = $this->_getRelateData($list, $fkey,
                    $config[self::ASSOC_TABLE], $condition, $at_self_key);
                $relate_list = $this->_getRelateData($assoc_list, $at_relate_key,
                    $config[self::RELATE_TABLE], $condition, $relate_table_key);
                $this->_assembleRelateData($assoc_list, $at_self_key, $list, $fkey);
                $this->_assembleRelateData($assoc_list, $at_relate_key, $relate_list, $relate_table_key);
                return $assoc_list;
            default:
                throw new \Exception('Wrong correlate type! [' . $config[self::RELATE_TYPE] . ']');
        }
    }

    /**
     * @param string $model_name
     * @return Correlate
     */
    protected function _getModel($model_name){
        return new $model_name;
    }

    /**
     * @param array $base_list  基准列表
     * @param string $base_fkey    基准key字段名
     * @param string $relate_model_name 关联表model名
     * @param array $condition  查询条件
     * @param string $relate_key    关联key名
     * @return array
     */
    protected function _getRelateData(Array $base_list, $base_fkey, $relate_model_name, Array $condition, $relate_key){
        $key_list = [];
        foreach ($base_list as $row) {
            $key_list[$row[$base_fkey]] = $row[$base_fkey];
        }
        $condition[$relate_key][Factory::IN] = $key_list;
        $relate_mdoel = $this->_getModel($relate_model_name);

        if($relate_mdoel instanceof Correlate){
            $relate_mdoel->relateOff();
        }
        $data = $relate_mdoel->getList($condition);
        if($relate_mdoel instanceof Correlate){
            $relate_mdoel->relateReset();
        }

        return $data;
    }

    protected function _assembleRelateData(&$multi_list, $multi_fkey, $one_list, $one_key){
        $indexed_one_list = [];
        foreach ($one_list as &$row) {
            $indexed_one_list[$row[$one_key]] = $row;
        }
        foreach ($multi_list as &$row) {
            $row[$multi_fkey] = $indexed_one_list[$row[$multi_fkey]];
        }
        //return $multi_list;
    }

    public function relateOn(){
        $this->_use_correlate = true;
    }

    public function relateOff(){
        $this->_use_correlate = false;
    }

    public function relateReset(){
        $this->_use_correlate = $this->_correlate_default;
    }

} 