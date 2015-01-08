<?php
/**
 * @name Factory.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-31
 * @version 0.01
 */

namespace GreenTea;


use GreenTea\Utility\String;

abstract class Factory {
    /**
     * 这种变相单例的存储一定要用静态变量！非静态根本无效果。
     * @var
     */
    protected static $_instances;

    /**
     * singleton version
     * @param $target
     * @param array $arguments
     * @return object
     */
    public function getShared($target, Array $arguments = null){
        if(isset(self::$_instances[$target])){
            return self::$_instances[$target];
        }
        self::$_instances[$target] = $this->get($target, $arguments);
        return self::$_instances[$target];
    }

    /**
     * =getInstance()
     * @param $target
     * @param array $arguments
     * @throws \Exception
     * @return object
     */
    public function get($target, Array $arguments = null){
        $classname = $this->getClassname(String::camelize($target));
        if(class_exists($classname)){
            $rc = new \ReflectionClass($classname);
            if($arguments === null){
                return $rc->newInstance();
            }else{
                return $rc->newInstanceArgs($arguments);
            }

        }else{
            throw new \Exception("Class[$classname] is Not Exist!");
        }
    }

    abstract public function getClassname($target);
} 