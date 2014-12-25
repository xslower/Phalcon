<?php
/**
 * @name Fields.php
 * @desc
 * @author 宋文峰(songwenfeng@baidu.com)
 * @date 13-10-29
 * @version 0.01
 */
namespace GreenTea\Model\Sql;

class Fields{

    public static function assembleRead($fields){
        if(!$fields) return '*';
        if(is_string(each($fields)['key'])){ //如果是关联数组，则表明是field-value型
            $fields = array_keys($fields);
        }
        return implode(',', $fields);
    }

    /**
     * 组装insert/update的fields
     * @param $fileds
     * @return string
     */
    public static function assembleWrite($fileds){
        if(!is_array($fileds)) return $fileds;
        $result = array();
        foreach ($fileds as $key => $val) {
            if(is_array($val)){
                $item = each($val); //在fields段中只支持一个节点，就是节点的key作为分类标识。
                $state = '';
                switch($item['key']){
                    case Factory::SELF_ADD:    //支持自增
                        $state = self::selfOP($key, $item['value'], '+');
                        break;
                    case Factory::SELF_SUB:    //支持自减
                        $state = self::selfOP($key, $item['value'], '-');
                        break;
                    default:
                        break;
                }
            }elseif(is_string($val)){
                $state = " `$key` = " . Helper::purifyValue($val);
            }else{ //非字符串也非数组，则是数字，可以不过虑
                $state = " `$key` = $val";
            }
            $result[] = $state;
        }
        return implode(',', $result);
        //return $result;
    }

    protected static function selfOP($key, $value, $op = '+'){
        $val = Helper::purifyValue($value);
        $state = " `$key` = `$key` $op $val ";
        return $state;
    }


}