<?php
/**
 * @name Cli.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-17
 * @version 0.01
 */

namespace GreenTea\DI;


use GreenTea\Utility\XArray;

class Cli extends \Phalcon\DI\FactoryDefault\CLI{
    public function __construct($config){
        parent::__construct();
        $config['log_path'] = XArray::fetchItem($config, 'log_dir', '/tmp/php/log') . '/cli/';
        Services::register($this, $config);

    }
} 