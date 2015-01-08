<?php
/**
 * @name global.php
 * @desc 全局配置文件
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-9
 * @version 0.01
 */

return array(
    'loader' => array(
        'controller' => '/app/controller',
        'test' => '/app/test',
        'test-controller' => '/app/test/controller',
        'model' => '/app/model',
        'library' => '/app/library',
        'service' => '/app/service',
        'plugin' => '/app/plugin',
        'task' => '/app/task',
    ),
    'namespace' => array(
        'Model' => '/app/model',
        'Library' => '/app/library',
        'Service' => '/app/service',
        'Plugin' => '/app/plugin',

        'Phalcon' => '/lib/Phalcon',
        'PhalconEx' => '/lib/PhalconEx',
        'GreenTea' => '/lib/GreenTea',
        'Aliyun' => '/lib/Aliyun',
        'Guzzle' => '/lib/Guzzle',
        'Symfony' => '/lib/Symfony',
        'Utility' => '/lib/Utility',

    ),
    'develop' => array(
        'mode' => 'dev',    //dev/test/online no defect
        'profiling' => true,
    ),
);