<?php
/**
 * @name Append.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-10
 * @version 0.01
 * TODO 是否必要增加手动加锁的支持
 */

namespace GreenTea\Model\Sql;


use GreenTea\Utility\XArray;

class Append{

    public static function assemble(Array $append){
        $groupBy = XArray::fetchItem($append, Factory::GROUP_BY, false);
        $orderBy = XArray::fetchItem($append, Factory::ORDER_BY, false);
        $limit = XArray::fetchItem($append, Factory::LIMIT, false);

        $strAppend = ' ';
        if($groupBy) $strAppend .= ' GROUP BY ' . $groupBy;
        if($orderBy) $strAppend .= ' ORDER BY ' . $orderBy;
        if($limit) $strAppend .= ' LIMIT ' . $limit;
        return $strAppend;
    }
} 