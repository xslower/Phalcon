<?php
/**
 * @name Connector.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-23
 * @version 0.01
 */

namespace GreenTea\Cache;


abstract class Connector {
    const PERSISTENT = 'persistent';
    const HOST = 'host';
    const PORT = 'port';
    protected static $_obj = [];
    protected function __construct(){

    }

    /**
     * @return \GreenTea\Cache\Connector
     */
    public static function getInstance(){
        $class = get_called_class();
        if(!isset(self::$_obj[$class]) || !self::$_obj[$class] instanceof static){
            self::$_obj[$class] = new static;
        }
        return self::$_obj[$class];
    }
    abstract public function connect(Array $options);
} 