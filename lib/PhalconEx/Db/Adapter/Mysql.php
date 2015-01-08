<?php
/**
 * @name Mysql.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-15
 * @version 0.01
 */

namespace PhalconEx\Db\Adapter;


use Phalcon\Db;

class Mysql extends \Phalcon\Db\Adapter\Pdo\Mysql{
    protected $_last_statement;

    public function query($statement, $placeholders = null, $datatypes = null){
        $this->_last_statement = $statement;
        $result = parent::query($statement, $placeholders, $datatypes);
        $result->setFetchMode(Db::FETCH_ASSOC);
        return $result->fetchAll();
    }

    public function execute($sqlStatement, $placeholders = null, $dataTypes = null){
        $this->_last_statement = $sqlStatement;
        return parent::execute($sqlStatement, $placeholders, $dataTypes);
    }

    public function switchDb($dbname){
        if($dbname) $this->execute('USE ' . $dbname);
    }

    public function switchTable($table){
        //do nothing
    }

    public function getLastSqlStatement(){
        return $this->_last_statement;
    }
} 