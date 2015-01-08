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

    /**
     * 组装select的fields
     * @param array $fields
     * @return string
     */
    public static function assembleRead(Array $fields){
        if(empty($fields)) return '*';
        if(current($fields) == '*') return '*';

        $strField = '';
        foreach ($fields as $key => $val) {
            if(is_string($key)){
                $strField .= ',' . self::handleField($key) . ' AS ' . self::handleField($val);
                continue;
            }
            $strField .= ',' . self::handleField($val);
        }
        return substr($strField, 1);
    }

    /**
     * 组装insert/update的fields
     * @param array $fields
     * @return string
     */
    public static function assembleWrite(Array $fields){
        if(!is_array($fields)) return $fields;
        $result = array();
        foreach ($fields as $key => $val) {
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

    protected static function getQuotes($field){
        //count(*)和field两种情况
        if(strpos($field, '(') !== false) return $field;
        return '`' . $field . '`';

    }

    protected static function handleField($field){
        if(!is_string($field)) return '';
        //像[ count(*) as num ]这种情况
        $slice = explode($field, ' ');
        switch(count($slice)){
            case 1:
                return self::getQuotes($field);
            case 3: //count(*) as num和field as num两种情况
                return self::getQuotes($slice[0]) . ' AS ' . self::getQuotes($slice[2]);
            default://error
                return $field;
        }
    }
}