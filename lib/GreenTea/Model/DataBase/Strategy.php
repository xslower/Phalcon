<?php
/**
 * @name Strategy.php
 * @desc 用于初始化db的分发策略的类
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/12/22 0022
 * @version 0.01
 */

namespace GreenTea\Model\DataBase;

use GreenTea\DI\Services;
use GreenTea\DI\Web as DI;
use GreenTea\Model\Distribute;

class Strategy {

    protected $_config_service = '';
    /**
     * @var \Greentea\Model\Distribute
     */
    protected $_strategy;

    /**
     * @var array
     * 这个静态数组是一种单例，当使用同一个db配置时，多个model使用的是唯一的distribute对象。
     */
    protected static $_strategy_instances = [];


    /**
     * @var \PhalconEx\Db\Adapter\Mysql
     * 用于临时存储driver
     */
    protected $_driver;


    public function __construct($config_service = ''){
        if(!$config_service){
            $config_service = $this->_config_service;
        }
        $di = DI::getDefault();
        $ds_factory = $di->getShared(Services::FACTORY_MODEL_DISTRIBUTE);
        if(!isset(self::$_strategy_instances[$config_service])){
            $arr_config = $di->getShared($config_service);
            self::$_strategy_instances[$config_service] =
                $ds_factory->get($arr_config['strategy'], [$arr_config]);
        }
        $this->_strategy = self::$_strategy_instances[$config_service];

    }

    protected function _getDriver(Array $factor){
        return $this->_strategy->getDistributedDriver($factor);
    }

    /**
     * @param $db
     * @param $table
     * @param $op_type
     * @return array
     */
    protected function _assembleFactors($db, $table, $op_type){
        $factor = [
            Distribute::FACTOR_DB_NAME => $db,
            Distribute::FACTOR_TABLE_NAME => $table,
            Distribute::OPT_OP_TYPE => $op_type,
        ];
        return $factor;
    }
} 