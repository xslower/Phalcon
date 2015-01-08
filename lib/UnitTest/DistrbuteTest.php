<?php
use GreenTea\Model\Distribute;

/**
 * @name DistrbuteTest.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/11/6 0006
 * @version 0.01
 */

class DistrbuteTest extends PHPUnit_Framework_TestCase{

    protected static $_db_config = [
            'strategy' => 'custom',
            'lifetime' => 1800,
            'driver' => 'mysql',
            'retry' => 2,
            'persistent' => false,

            'nodes' => array(
                10 => [
                    'host' => '10.4.122.147',
                    'port' => 8801,
                    'username' => 'greentea',
                    'password' => 'greentea',
                    'dbname' => 'test',
                    'role' => 'master',     //master accept both read and write operation
                    'weight' => 2,
                ],
//                20 => [
//                    'host' => '10.4.122.147',
//                    'port' => 8801,
//                    'username' => 'greentea',
//                    'password' => 'greentea',
//                    'dbname' => 'greentea',
//                    'role' => 'slave',      //slave only accept read operation
//                    'master' => 10,         //slave of which master
//                    'weight' => 4,
//                ],
            ),
        ];

    protected static $_cache_config = [
        'strategy' => 'hash',
        'lifetime' => 1800,
        'persistent' => false,
        'driver' => 'redis',
        'retry' => 2,

        'nodes' => array(
            10 => [
                'host' => '127.0.0.1',
                'port' => 6379,
                'role' => 'master',     //master accept both read and write operation
                'weight' => 2,
            ],
            20 => [
                'host' => '127.0.0.1',
                'port' => 6379,
                'role' => 'master',      //slave only accept read operation
                'master' => 10,         //slave of which master
                'weight' => 2,
            ],
        )
    ];

    protected static $_memd_hash = [
        'strategy' => 'memcached_hash',
        'lifetime' => 1800,
        'persistent' => false,
        'driver' => 'memcached',
        'retry' => 2,

        'nodes' => array(
            10 => [
                'host' => '127.0.0.1',
                'port' => 11211,
                'role' => 'master',     //master accept both read and write operation
                'weight' => 2,
            ],
            20 => [
                'host' => '127.0.0.1',
                'port' => 11211,
                'role' => 'slave',      //slave only accept read operation
                'master' => 10,         //slave of which master
                'weight' => 4,
            ],
        )
    ];

    protected $_factors = [
        Distribute::FACTOR_DB_NAME => 'Db',
        Distribute::FACTOR_TABLE_NAME => 'Table',
        Distribute::OPT_OP_TYPE => Distribute::OP_READ
    ];

    public function testHash(){
        $hash = new \GreenTea\Model\Distribute\Hash(self::$_cache_config);
        $redis = $hash->getDistributedDriver($this->_factors);
    }

    public function testCustom(){
        $custom = new Distribute\Custom(self::$_db_config);
        $factors = [
            Distribute::FACTOR_DB_NAME => 'test',
            Distribute::FACTOR_TABLE_NAME => 'mytest',
            Distribute::OPT_OP_TYPE => Distribute::OP_READ
        ];
        $mysql = $custom->getDistributedDriver($factors);
    }

    public function testMemcachedHash(){
        $mh = new Distribute\MemcachedHash(self::$_memd_hash);
        $memcached = $mh->getDistributedDriver($this->_factors);
    }
} 