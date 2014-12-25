<?php
/**
 * @name IStatement.php
 * @desc 
 * @author 宋文峰(songwenfeng@baidu.com)
 * @date 13-10-29
 * @version 0.01
 */
namespace GreenTea\Model\Sql;

abstract class IStatement {

    abstract public function assemble($table, $fields = null, $condition = null, $append = null);
}