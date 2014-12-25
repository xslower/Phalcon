<?php
/**
 * @name Cachable.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-17
 * @version 0.01
 */

namespace GreenTea\Model\DataBase;

use GreenTea\DI\Services;
use GreenTea\DI\Web as DI;

class Cachable extends Correlate{

    //缓存策略：
    //关闭缓存
    const CACHE_NONE = 1;
    //以行为单位存储，当获取列表时，使用主键值逐个获取所有的记录
    const CACHE_ROW = 2;
    //以块为单位存储，主要用于表比较小，想直接缓存整个表的情况
    const CACHE_PACKAGE = 3;

    //延迟写，先写缓存，然后定期同步到数据库
    //TODO,此功能待实现
    const WRITE_PERIOD = 1;

    protected $_cache_service = Services::SERVICE_DB_CACHE;

    protected $_cache_strategy = self::CACHE_ROW;

    protected $_delay_write = self::WRITE_PERIOD;

    protected $_lifetime = 1800;

    /**
     * @var \GreenTea\Model\NoSQL
     */
    protected $_cache;

    protected $_callback = [];

    public function __construct(){
        parent::__construct();
        $this->_cache = DI::getDefault()->getShared($this->_cache_service);
        $this->_cache->setDbName($this->_db_name);
        $this->_cache->setTableName($this->_table_name);
        $this->_initCallback();
    }

    protected function _initCallback(){
        $this->_callback[self::CACHE_NONE]['read'] = function(Array &$channel, Array $condition){return null;};
        $this->_callback[self::CACHE_NONE]['write'] = function(Array &$channel, $result){};
        $this->_callback[self::CACHE_PACKAGE]['read'] = function(Array &$channel, Array $condition){
            $this->_cachePrepare($condition);
            $cache_key = $this->_generateHashKey($condition);
            $channel['cache_key'] = $cache_key;
            $result = $this->_cache->get($cache_key);
            return $result;
        };
        $this->_callback[self::CACHE_PACKAGE]['write'] = function(Array &$channel, $result){
            $key = $channel['cache_key'];
            $this->_cache->set($key, $result, $this->_lifetime);
        };
        $this->_callback[self::CACHE_ROW]['read'] = function(Array &$channel, Array $condition){
            $this->_cachePrepare($condition);
            $primary_key = $this->_getPkValue($condition);
            $cache_key = $primary_key;
            if(!$primary_key){ //如果不是主键查找，则需要二次获取
                $cache_key = $this->_generateHashKey($condition);
            }
            $result = $this->_cache->get($cache_key);
            if($result){
                if(!$primary_key){
                    $keys = $result;
                    $result = $this->_cache->multiGet($keys);
                    //如果有某个记录无缓存，则重新查询DB
                    $is_complete = true;
                    foreach ($result as $v) {
                        if(!$v) {
                            $is_complete = false;
                            break;
                        }
                    }
                    if($is_complete){
                        return $result;
                    }
                }else{ //如果是主键查找，则需要为row添加一层数组
                    return [$result];
                }
            }
            $channel['primary_key'] = $primary_key;
            $channel['cache_key'] = $cache_key;
            return null;
        };
        $this->_callback[self::CACHE_ROW]['write'] = function(Array &$channel, &$result){
            $primary_key = $channel['primary_key'];
            $cache_key = $channel['cache_key'];
            $keys = [];
            $itmes = [];
            foreach ($result as $v) { //无论取出的是单条还是多条，统统加key处理
                $pk = $this->_getPkValue($v);
                $keys[] = $pk;
                $itmes[$pk] = $v;
            }
            $this->_cache->multiSet($itmes);
            if(!$primary_key){ //如果多条则缓存索引keys
                $this->_cache->set($cache_key, $keys, $this->_lifetime);
            }
            $result = $itmes; //把结果转为带key的
        };
    }

    /**
     * 通过查询条件生成一个缓存key
     * @param array $condition
     * @return string
     */
    protected function _generateHashKey(Array $condition){
        $hash_key = json_encode($condition);
        if(!$hash_key) $hash_key = 'all';
        return md5($hash_key);

    }

    /**
     * 缓存的准备动作：例如切换表名
     * @param array $fields_or_condition
     */
    protected function _cachePrepare(Array $fields_or_condition){
        $table = $this->getTableName($fields_or_condition);
        if($table != $this->_table_name) { //如果是分表，则需要随时更换表名
            $this->_cache->setTableName($table);
        }
    }

    /**
     * @param array $fields
     * 插入数据时可能有的字段使用DB默认值，补全之，以方便写缓存
     * @comment 写DB之后进行这种操作价值不大，因为数据可能多次写也不读。
     *  反而如果能用做缓冲写的价值就大多了。
     */
    protected function _populateDefaultIntoFields(Array &$fields = []){
        $columns = $this->_getTableColumns();
        foreach ($columns as $k => $v) {
            if(isset($fields[$k])) continue;
            if ($v[self::COLUMNS_DEFAULT] == self::COLUMNS_DEFAULT_TIMESTAMP) {
                $fields[$k] = date('Y-m-d H:i:s');
            } elseif ($v[self::COLUMNS_DEFAULT] == self::COLUMNS_DEFAULT_NULL) {
                $fields[$k] = null;
            } else {
                $fields[$k] = $v[self::COLUMNS_DEFAULT];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function insertOrUpdate(Array $fields){
        $pk = parent::insertOrUpdate($fields);
        if($this->_cache_strategy == self::CACHE_ROW) {
            //这里数据有可能不全，例如创建时间，id，所以无法缓存
            //$this->_cachePrepare($fields);
            //$this->_cache->set($pk, $fields);
        }
        return $pk;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(Array $fields){
        $pk = parent::insert($fields);
        if($this->_cache_strategy == self::CACHE_ROW) {
            //这里数据有可能不全，例如创建时间，id，所以无法缓存
            //$this->_cachePrepare($fields);
            //$this->_cache->set($pk, $fields);
        }
        return $pk;
    }

    /**
     *  对上层getList增加缓存行为
     * {@inheritdoc}
     */
    public function getList(Array $condition = [], Array $append = [], Array $fields = []){
        $channel = [];
        $result = call_user_func_array($this->_callback[$this->_cache_strategy]['read'],
            [&$channel, &$condition]);
        if($result) return $result;

        if($this->_cache_strategy != self::CACHE_NONE){
            //如果开了缓存则强制使用全部字段，不然缓存后会取不到没被缓存的字段
            $fields = ['*'];
        }
        $result = parent::getList($condition, $append, $fields);

        call_user_func_array($this->_callback[$this->_cache_strategy]['write'],
            [&$channel, &$result]);

        return $result;
    }

    //TODO 考虑给getGroup()和getCount()加上缓存，以及考虑是否能直接在select上加缓存，这样getGroup就自动增加了缓存
} 