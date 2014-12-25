<?php
/**
 * @name Hash.php
 * @desc 其实此算法通过配置权重也能达到一致性哈希的效果。
 * 例如：在增加节点时，保证权重之和不变，让前一个节点或后一个节点分出一部分权重给新节点即可。
 *  减少节点时，把减的节点加到前/后节点上即可。
 * 例：1(weight=10)/2(weight=30)，加一个节点到2后面=> 1(weight=10)/2(weight=15)/3(weight=15)，
 *  这样就不会影响节点1的数据。
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-30
 * @version 0.01
 */

namespace GreenTea\Model\Distribute;


use GreenTea\Model\Distribute;

class Hash extends Distribute{

    public function getDistributedDriver(Array $factors){
        //why + 1? because the scope of remainder is 0~(N-1)，what we want is 1~N. so
        $remainder = crc32($this->getFactor($factors)) % self::WEIGHT_CEILING + 1;
        $key = $this->getTargetKey($factors[self::OPT_OP_TYPE], $remainder);
        $retry = 3;
        do{
            $driver = $this->tryConnect($key, $factors);
        }while(!$driver && --$retry); //if connect failed, retry 3 times.
        if(!$driver){
            throw new \Exception('Can not connect to server! ');
        }

        return $driver;
    }

    private function getFactor($factors){
        $f = $factors[self::FACTOR_DB_NAME] . $factors[self::FACTOR_TABLE_NAME];
        if(!$f) throw new \Exception('Need DB Name or Table Name to decied which Node to connection!');
        return $f;
    }


} 