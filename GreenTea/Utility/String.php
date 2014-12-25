<?php
/**
 * @name String.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-11
 * @version 0.01
 */

namespace GreenTea\Utility;


class String {
    /**
     * 下划线命名改为驼峰命名
     * @param $string
     * @return string
     * @comment Phalcon\Test::camelize对无下划线的字符串也会修改，导致意料外的行为
     */
    public static function camelize($string){
        if(strpos($string, '_') === false){
            return ucfirst($string);
        }
        $words = explode('_', $string);
        array_walk($words, function(&$val, &$key){
            $val = ucfirst($val);
        });
        return implode('', $words);
    }
} 