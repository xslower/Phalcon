<?php
/**
 * @name ISub.php
 * @desc 
 * @author 宋文峰(songwenfeng@baidu.com)
 * @date 13-10-29
 * @version 0.01
 */
namespace GreenTea\Model\Sql;

class Helper {

    /**
     * 根据条件对值进行转化
     * @param $value
     * @return string
     */
    public static function purifyValue($value){
        $out_val = '';
        if(is_array($value)){
            //暂不支持数组
        }elseif(is_string($value)){
            $value = addslashes($value);
            $has_field = false;
            //通过能否找到两个`符号来判断是否存在字段名
            $pos1 = strpos($value, '`');
            if($pos1 !== false){
                $pos2 = strpos($value, '`', $pos1 + 1);
                if($pos2 !== false){
                    $has_field = true;
                }
            }
            if($has_field){
                $out_val = $value;
            }else{
                $out_val = "'" . $value . "'";
            }
        }else{
            $out_val = $value;
        }
        return $out_val;
    }

    public static function kvPair($key, $value, $op){
        $val = Helper::purifyValue($value);
        $state = " `$key` $op $val ";
        return $state;
    }

    public static function equal($key, $val){
        return Helper::kvPair($key, $val, '=');
    }


}