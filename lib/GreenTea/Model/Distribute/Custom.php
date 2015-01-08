<?php
/**
 * @name Custom.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-10
 * @version 0.01
 * 对于哪个表在哪个DB上，这属于运维逻辑，不属于业务逻辑，所以不应该加到代码中让开发者察觉(不能在model中配置)。
 * 可以增加一个分发策略：通过配置文件来设定，某个Model(表)是否进行分布式(分表)，在哪些台DB上。
 */

namespace GreenTea\Model\Distribute;


use GreenTea\DI\Services;
use GreenTea\DI\Web as DI;
use GreenTea\Model\Distribute;
use GreenTea\Utility\XArray;

class Custom extends Distribute{
    public function getDistributedDriver(Array $factors){
        if(!isset($factors[self::FACTOR_TABLE_NAME])){
            throw new \Exception('Need factors: table_name!');
        }
        $table_name = $factors[self::FACTOR_TABLE_NAME];
        $di = DI::getDefault();
        $db_custom_config = $di->getShared(Services::CONFIG_DB_CUSTOM);
        if(!isset($db_custom_config[$table_name])){
            //拆表的每个表名都不一样，都需要指定db节点；特殊表也需要指定db节点；
            $remainder = 1; //剩余的表都使用第一个节点
            $key = $this->getTargetKey($factors[self::OPT_OP_TYPE], $remainder);
        }else{
            $key_origin = $db_custom_config[$table_name];
            if(is_array($key_origin)){
                $key = $key_origin[self::OPT_OP_TYPE];
            }else{
                $key = $key_origin;
            }
        }

        $retry = 3;
        do{
            $driver = $this->tryConnect($key, $factors);
        }while(!$driver && --$retry); //if connect failed, retry 3 times.
        if(!$driver){
            throw new \Exception('Can not connect to server! ');
        }

        return $driver;
    }
} 