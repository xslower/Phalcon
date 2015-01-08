<?php
/**
 * @name cli.php
 * @desc
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-17
 * @version 0.01
 */

ini_set('xdebug.show_exception_trace', 0);

define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));
//Read the configuration
$config = require(ROOT_PATH . '/app/config/global.php');

foreach ($config['loader'] as &$v) {
    $v = ROOT_PATH . $v;
}
foreach ($config['namespace'] as &$v) {
    $v = ROOT_PATH . $v;
}
$di_config = require(ROOT_PATH . '/app/config/di.php');
foreach ($di_config as &$v) {
    if(is_array($v)) continue;
    if(substr($v, 0, 4) == '/app'){
        $v = ROOT_PATH . $v;
    }
}
$config['loader']['unit_test'] = ROOT_PATH . '/lib/UnitTest/';
$config['loader']['unit_test_model'] = ROOT_PATH . '/lib/UnitTest/model';

//Register an autoloader
$loader = new Phalcon\Loader();
$loader->registerDirs($config['loader']);
$loader->registerNamespaces($config['namespace']);
//var_dump($loader->getNamespaces());
$loader->register();

\GreenTea\DI\Cli::setDefault(new GreenTea\DI\Cli($di_config));


//end profiling