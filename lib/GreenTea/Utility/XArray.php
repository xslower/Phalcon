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
    public static function fetchItem(Array $array, $key, $default = null) {
        if (isset($array[$key])) {
            return $array[$key];
        } else {
            if ($default == 'KEY') {
                return $key;
            } else {
                return $default;
            }
        }
    }

    public static function fetchRowsInList(Array $list, $key_name, $key_val) {
        $ret = [];
        foreach ($list as $v) {
            if ($v[$key_name] == $key_val)
                $ret[] = $v;
        }
        return $ret;
    }

    /**
     * 判断是否为多维数组
     * @param array $arr
     */
    public static function isTwoDimension(Array $arr) {
        return is_array(current($arr));
    }

    /**
     * 把类似数据库格式的2维表状数组，转化为树型的多维数组
     * @param array $list   记录列表，一般是数据库返回的2维列表。
     * @param array $key    字段数组，最高的节点在最前面。
     * @param $val_key  如果设置了val_key，则树叶节点不是列表中的一条记录(row数组)，而是数组中的一个值
     * @return array
     * @comment 重构后，虽然支持无限层级，而且优雅点。但是可读性太差了。
     */
    public static function listToTree(Array $list, Array $key, $val_key = null) {
        $ret = [];
        if(empty($key)){
            return $ret;
        }
        $c = count($key);
        foreach ($list as $row) {
            $val = $val_key === null ? $row : $row[$val_key];
            $tmp = &$ret;
            for ($i = 0; $i < $c; $i++) { //逐级下移，使用引用可以同时修改其值
                if (isset($tmp[$row[$key[$i]]])) {
                    $tmp = &$tmp[$row[$key[$i]]];
                } else
                    break;
            }
            if ($i == $c) { //说明给定的key的组合不是unique，有重复的，则使用数字索引
                if (self::isTwoDimension($tmp)) {
                    $tmp[] = $val;
                } else { //如果$tmp指向的不是2维数组，则表明是第一次重复进入此节点，需要把原节点的数组跟新$val合并为一个2维数组
                    $vt = [$tmp];
                    $vt[] = $val;
                    $tmp = $vt;
                }
                break;
            }
            for ($j = $c - 1; $j > $i; $j--) { //逐级向上形式多维数组
                $vt = [];
                $vt[$row[$key[$j]]] = $val;
                $val = $vt;
            }
            $tmp[$row[$key[$i]]] = $val;
        }
        return $ret;
    }

    /**
     * 支持2维数据key查找
     * @param array $array
     * @param $key
     * @return mixed
     */
    public static function searchKey(Array $array, $key) {
        if (isset($array[$key])) {
            return $array[$key];
        }
        foreach ($array as $v) {
            if (is_array($v)) {
                if (isset($v[$key])) {
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
    public static function replace(Array $left, Array $right) {
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
    static public function getChangedColumn($old, $new) {
        $changed = array();
        $confict = array();
        foreach ($new as $k => $v) {
            //如果记录为空，则跳过
            if ($v == '')
                continue;
            if (isset($old[$k])) {
                if ($old[$k] != $v)
                    $changed[$k] = $v;
            }else {
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
        if ($asc)
            $x = 1;
        else
            $x = -1;
        $y = 0 - $x;
        return uasort($array, function($a, $b) use($key, $asc, $x, $y) {
            if ($a[$key] == $b[$key])
                return 0;
            return ($a[$key] > $b[$key]) ? $x : $y;
        });
    }

    /**
     * 去除数组中指定的数值
     * @param array $array
     * @param array $charlist
     */
    public static function trim(Array &$array, Array $charlist = array('', null)) {
        foreach ($array as $k => $v) {
            foreach ($charlist as $char) {
                if ($v === $char) {
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
    public static function diff(Array $arr1, Array $arr2, $keys) {
        return array_udiff($arr1, $arr2, function($a, $b) use($keys) {
            if (!is_array($keys))
                $keys = array($keys);
            $ret = 0;
            foreach ($keys as $k) {
                if ($a[$k] === $b[$k])
                    continue;
                $ret = $a[$k] > $b[$k] ? 1 : -1;
                break;
            }
            return $ret;
        });
    }

}
