<?php
/**
 * @name cache.php
 * @desc
 * @lifetime //if =0, then if any service crashed, it's still availabe for strategy.
 *              In other words, 'lifetime' is the cache time of data
 * @comment if u want the change of this configuration take effect,
 *  u need modify the first node key or it's host and port
 */

return array(
    'strategy' => 'hash',
    'lifetime' => 1800,
    'driver' => 'redis',
    'retry' => 2,

    'nodes' => array(
        10 => array(
            'host' => '182.92.103.11',
            //'port' => 11211,
            'role' => 'master',     //master accept both read and write operation
            'weight' => 2,
        ),
//        20 => array(
//            'host' => '10.4.122.147',
//            //'port' => 11211,
//            'role' => 'slave',      //slave only accept read operation
//            'master' => 10,         //slave of which master
//            'weight' => 4,
//        ),
//        30 => array(
//            'host' => '10.4.122.147',
//            //'port' => 11211,
//            'role' => 'standby',    //standby only accept read operation, and when master crashed,
//                                    //standby could be pomoted to be the new master
//            'master' => 10,
//            'weight' => 5,
//        )
    ),
);