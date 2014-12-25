<?php
/**
 * @name Insert.php
 * @desc 
 * @author 宋文峰(songwenfeng@baidu.com)
 * @date 13-10-29
 * @version 0.01
 */
namespace GreenTea\Model\Sql\Statement;

use GreenTea\Model\Sql\Fields;
use GreenTea\Model\Sql\IStatement;

class Insert extends IStatement{
    /**
     * @param mixed $table
     * @param array $fields
     * @param array $condition
     * @param array $append
     * @return string
     */
    public function assemble($table, $fields = null, $condition = null, $append = null){
        $str_fields = Fields::assembleWrite($fields);
        $statement = "INSERT `$table` SET $str_fields";
        return $statement;
    }
}