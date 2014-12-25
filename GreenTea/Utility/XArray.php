<?php
/**
 * @name Arr.php
 * @desc 
 * @author 宋文峰(songwenfeng@baidu.com)
 * @date 13-10-15
 * @version 0.02
 */
namespace GreenTea\Utility;

class XArray {

    /**
     * 返回数组中的某个值，如果不存在返回default或者key
     * @param array $array
     * @param $key
     * @param string $default
     * @return mixed
     */
    public static function fetchItem(Array $array, $key, $default = null){
        if(isset($array[$key])){
            return $array[$key];
        }else{
            if($default == 'KEY'){
                return $key;
            }else{
                return $default;
            }
        }
    }

    public static function fetchRowsInList(Array $list, $key_name, $key_val){
        $ret = [];
        foreach ($list as $v) {
            if($v[$key_name] == $key_val) $ret[] = $v;
        }
        return $ret;
    }
    /**
     * 把类似数据库格式的2维表状数组，转化为树型的多维数组
     * @param array $list
     * @param array $key
     * @param $val_key
     * @return array
     */
    public static function listToTree(Array $list, Array $key, $val_key = null){
        $ret = [];
        $c = count($key);
        foreach ($list as $row) {
            $val = $val_key === null ? $row : $row[$val_key];
            switch($c){
                case 1:
                    $ret[$row[$key[0]]] = $val;
                    break;
                case 2:
                    $ret[$row[$key[0]]][$row[$key[1]]] = $val;
                    break;
                case 3:
                    $ret[$row[$key[0]]][$row[$key[1]]][$row[$key[2]]] = $val;
                    break;
                case 4:
                    $ret[$row[$key[0]]][$row[$key[1]]][$row[$key[2]]][$row[$key[3]]] = $val;
                    break;
                default:
                    break;
            }
        }
        return $ret;
    }

    /**
     * 支持2维数据key查找
     * @param array $array
     * @param $key
     * @return mixed
     */
    public static function searchKey(Array $array, $key){
        if(isset($array[$key])){
            return $array[$key];
        }
        foreach ($array as $v) {
            if(is_array($v)){
                if(isset($v[$key])){
                    return $v[$key];
                }
            }
        }
        return false;
    }

    /**
     * 实现PHP5.3之后的array_replace功能
     * @param array $left
     * @param array $right
     * @return array
     */
    public static function replace(Array $left, Array $right){
        foreach ($right as $k => $v) {
            $left[$k] = $v;
        }
        return $left;
    }


    /**
     * 求new数组相对old数组值发生变化的部分
     * @param $old
     * @param $new
     * @return mixed
     */
    static public function getChangedColumn($old, $new){
        $changed = array();
        $confict = array();
        foreach ($new as $k => $v) {
            //如果记录为空，则跳过
            if($v == '') continue;
            if(isset($old[$k])){
                if($old[$k] != $v) $changed[$k] = $v;
            }else{
                $confict[$k] = $v;
            }
        }
        $ret['changed'] = $changed;
        $ret['conflict'] = $confict;
        return $ret;
    }

    /**
     * 以二维数组中的某个字段值，对其进行排序
     * @param array $array
     * @param $key
     * @param bool $asc
     * @return array
     */
    public static function sort2D(array $array, $key, $asc = true) {
        if($asc) $x = 1;
        else $x = -1;
        $y = 0 - $x;
        return uasort($array, function($a, $b) use($key, $asc, $x, $y){
            if($a[$key] == $b[$key]) return 0;
            return ($a[$key] > $b[$key]) ? $x : $y;
        });
    }

    /**
     * 去除数组中指定的数值
     * @param array $array
     * @param array $charlist
     */
    public static function trim(Array &$array, Array $charlist = array('', null)){
        foreach ($array as $k => $v) {
            foreach ($charlist as $char) {
                if($v === $char){
                    unset($array[$k]);
                    break;
                }
            }
        }

    }

    /**
     * 2-dimension array compare
     * @param array $arr1
     * @param array $arr2
     * @param mixed $keys
     * @return array
     */
    public static function diff(Array $arr1, Array $arr2, $keys){
        return array_udiff($arr1, $arr2, function($a, $b) use($keys){
            if(!is_array($keys)) $keys = array($keys);
            $ret = 0;
            foreach ($keys as $k) {
                if($a[$k] === $b[$k]) continue;
                $ret = $a[$k] > $b[$k] ? 1 : -1;
                break;
            }
            return $ret;
        });
    }

}