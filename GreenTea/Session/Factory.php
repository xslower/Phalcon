<?php

namespace GreenTea\Session;

class Factory{
    protected static $_instances;
    public static function getHandler($target, Array $arguments = null){
        if(isset(self::$_instances[$target])){
            return self::$_instances[$target];
        }
        $class = '\GreenTea\Session\Adapter\\' . \GreenTea\Utility\String::camelize($target);
        self::$_instances[$target] = new $class($arguments);
        return self::$_instances[$target];
    }
} 