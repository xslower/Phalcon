<?php
/**
 * @name Condition.php
 * @desc 
 * @author 宋文峰(songwenfeng@baidu.com)
 * @date 13-10-29
 * @version 0.01
 */
namespace GreenTea\Model\Sql;

class Condition{

    /**
     * 正常有两种用法，IN和BETWEEN有第三种简化用法。
     * 格式：condition = [
     *  'field1'  => [ '>' => 123 ];
     *
     *  'field2'  => [ 'BETWEEN' => [1,9] ];
     *
     *  'field3'  => [ 'IN' => [1,2,3] ];
     * ]
     * @param array $conds
     * @param string $relation
     * @return null|string
     */
    public static function assemble(Array $conds, $relation = 'AND'){
        //如果是空数组直接返回
        if(empty($conds)) return null;

        $condition = array();
        foreach ($conds as $key => $val) {
            if($val === null) continue;
            $state = '';
            if(!is_array($val)){
                $state = Helper::equal($key, $val);
                $condition[] = $state;
            }else{
                //当key值为or或and时，说明其为子条件关系，进行递归计算。
                if($key == Factory::_OR_){
                    $state = '(' . self::assemble($val, 'OR') . ')';
                    $condition[] = $state;
                }elseif($key == Factory::_AND_){
                    $state = '(' . self::assemble($val) . ')';
                    $condition[] = $state;
                }else{
                    //下面的逻辑就是默认如果field后面跟的是数组，则必须数组的key是操作符，>=、<、IN之类的
                    foreach ($val as $_op => $_v) {
                        $state = self::unify($key, $_op, $_v);
                        $condition[] = $state;
                    }
                }
            }
        }
        foreach ($condition as $_k => $_v) {
            if(!$_v){
                unset($condition[$_k]);
            }
        }
        if(empty($condition)) return '';
        else {
            return implode( ' ' . $relation . ' ', $condition);
        }
    }

    /**
     * 统一处理
     * @param $key
     * @param $op
     * @param $val
     * @return string
     */
    protected static function unify($key, $op, $val){
        $state = '';
        if($val === null){ //如果为null则表明为无效条件，去除
            return $state;
        }
        switch($op){
            case 'IN':
                $state = self::in($key, $val);
                break;
            case 'BETWEEN':
                $start = array_shift($val);
                $end = array_shift($val);
                $state = self::between($key, $start, $end);
                break;
            case 'LIKE':
                $state = self::like($key, $val);
                break;
            default:
                $state = Helper::kvPair($key, $val, $op);
                break;
        }
        return $state;
    }

    protected static function in($key, $value){
        if(!$value) return '';
        if(is_array($value)){
            foreach ($value as &$v) {
                $v = Helper::purifyValue($v);
            }
            $str_val = implode(',', $value);
        }else{
            $str_val = Helper::purifyValue($value);
        }
        $state = " `$key` IN ($str_val) ";
        return $state;
    }

    protected static function between($key, $start, $end){
        $s = Helper::purifyValue($start);
        $e = Helper::purifyValue($end);
        $state = " `$key` BETWEEN $s AND $e ";
        return $state;
    }

    protected static function like($key, $value){
        //过滤字符串，并去除自动加上的引号',以便之后增加通配符%
        $val = trim(Helper::purifyValue($value), "'");

        if(strpos($val, '%') === false){ //用LIKE时，如果值中没有加通配符则自动加上
            $val = '%' . $val . '%';
        }
        $state = " `$key` LIKE '$val' ";
        return $state;
    }

}