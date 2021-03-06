<?php
/**
 * @name Delete.php
 * @desc 
 * @author 宋文峰(songwenfeng@baidu.com)
 * @date 13-10-29
 * @version 0.01
 */
namespace GreenTea\Model\Sql\Statement;

use GreenTea\Model\Sql\Condition;
use GreenTea\Model\Sql\IStatement;

class Delete extends IStatement{
    /**
     * @param mixed $table
     * @param array $fields
     * @param array $condition
     * @param array $append
     * @return string
     */
    public function assemble($table, Array $fields = [], Array $condition = [], Array $append = []){
        $str_condition = Condition::assemble($condition);
        if($str_condition){
            $str_condition = ' WHERE ' . $str_condition;
        }
        $statement = "DELETE FROM `$table` $str_condition";
        return $statement;
    }
}