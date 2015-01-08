<?php
/**
 * @name InsertOrUpdate.php
 * @desc 
 * @author 宋文峰(songwenfeng@baidu.com)
 * @date 13-10-29
 * @version 0.01
 */
namespace GreenTea\Model\Sql\Statement;

use GreenTea\Model\Sql\Factory;
use GreenTea\Model\Sql\Fields;
use GreenTea\Model\Sql\IStatement;

use GreenTea\Utility\XArray;

class InsertOrUpdate extends IStatement{

    /**
     * @param mixed $table
     * @param array $fields
     * @param array $condition
     * @param array $append
     * @return string
     */
    public function assemble($table, Array $fields = [], Array $condition = [], Array $append = []){
        $insert_fields = XArray::fetchItem($fields, Factory::INSERT_FIELDS, false);
        $update_fields = XArray::fetchItem($fields, Factory::UPDATE_FIELDS, false);
        if($insert_fields === false || $update_fields === false){
            $insert_fields = $fields;
            $update_fields = $fields;
        }
        $str_insert_fields = Fields::assembleWrite($insert_fields);
        $str_update_fields = Fields::assembleWrite($update_fields);
        $statement = "INSERT `$table` SET $str_insert_fields ON DUPLICATE KEY UPDATE $str_update_fields";
        return $statement;
    }
}