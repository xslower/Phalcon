<?php
/**
 * @name Transaction.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/12/22 0022
 * @version 0.01
 */

namespace GreenTea\Model\DataBase;

use GreenTea\DI\Services;
use GreenTea\Model\DataBase;
use GreenTea\DI\Web as DI;
use GreenTea\Model\Distribute;
use GreenTea\Utility\XArray;

class Transaction extends Strategy{
    /**
     * @var \PhalconEx\Db\Adapter\Mysql
     */
    protected $_driver = null;

    /**
     * 因为分发策略不依据dbname，所以不管它
     * @throws \Exception
     */
    public function __construct(){
        if(func_num_args() < 1){
            throw new \Exception('Need at least One Model to start Transaction!');
        }
        $args = func_get_args();
        $this->_validate($args);
        parent::__construct(Services::CONFIG_DB);
        $this->_initDriver('', $args[0]->getTableName());

    }

    /**
     * 判断多个model是否同属一个db节点，不是则无法开启事务
     * @param array $models
     * @throws \Exception
     */
    protected function _validate(Array $models){
        $di = DI::getDefault();
        $db_custom_config = $di->getShared(Services::CONFIG_DB_CUSTOM);
        $first_node_id = 0;
        $i = 1;
        foreach ($models as $arg) {
            if(!$arg instanceof DataBase){
                throw new \Exception('Parameter Error! Need Model!');
            }
            $tablename = $arg->getTableName();
            $node_id = XArray::fetchItem($db_custom_config, $tablename, 0);
            if($i != 0){ //从第二个参数开始要逐个与第一个的比较
                if($first_node_id != $node_id){
                    throw new \Exception('Transaction Must run in the same Db Node!');
                }
            }else{
                $first_node_id = $node_id;
            }
            $i++;
        }
    }

    protected function _initDriver($db, $table){
        $this->_driver = $this->_getDriver($this->_assembleFactors($db, $table, Distribute::OP_WRITE));
    }

    /**
     * @return \PhalconEx\Db\Adapter\Mysql
     */
    public function getDriver(){
        return $this->_driver;
    }

    public function begin(){
        $this->_driver->begin();
    }

    public function rollback(){
        $this->_driver->rollback();
    }

    public function commit(){
        $this->_driver->commit();
    }
} 