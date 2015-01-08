<?php
/**
 * @name di .php
 * @desc 初始化di需要的配置
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-30
 * @version 0.01
 */
return [

    'nosql_config' => '/app/config/cache.php',
    'db_config' => '/app/config/db.php',
    'db_custom' => '/app/config/db_custom.php',
    'log_path' => '/tmp/php/log/',
    'view' => '/app/view',
    'session' => [
        'path' => 'tcp://10.4.122.147:6379',
        'lifetime' => 3600,
        'cookie_lifetime' => 24 * 3600,
    ],
    'queue' => [
        'host' => '10.4.122.147',
        'port' => 6379,
        'persistent' => false,
    ],
    'url' => [
        'base_url' => 'http://gproxy.wenfeng.dev:2000/',
    ]

];