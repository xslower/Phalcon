<?php
/**
 * @name SqlTest.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/11/6 0006
 * @version 0.01
 */

class SqlTest extends PHPUnit_Framework_TestCase{
    public function __construct(){

    }

    public function testSelect(){
        $select = new \GreenTea\Model\Sql\Statement\Select();
        $condition['a'] = 'b';
        $condition['id'] = 5;
        $sql = $select->assemble('some_table', [], $condition);
        $this->assertStringStartsWith('SELECT', $sql);
    }

    public function testInsert(){
        $insert = new \GreenTea\Model\Sql\Statement\Insert();
        $fields['name'] = 'hahaha';
        $sql = $insert->assemble('table', $fields);
        $this->assertStringStartsWith('INSERT', $sql);
    }

    public function testUpdate(){
        $update = new \GreenTea\Model\Sql\Statement\Update();
        $fields['a'] = 'b';
        $condition['id'] = 5;
        $sql = $update->assemble('table', $fields, $condition);
        $this->assertStringStartsWith('UPDATE', $sql);
    }

    public function testInsertUpdate(){
        $iu = new \GreenTea\Model\Sql\Statement\InsertOrUpdate();
        $fields['a'] = 'b';
        $fields['id'] = 5;
        $sql = $iu->assemble('table', $fields);
        $this->assertStringStartsWith('INSERT', $sql);
    }

    public function testDelete(){
        $delete = new \GreenTea\Model\Sql\Statement\Delete();
        $condition['id'] = 5;
        $sql = $delete->assemble('table', [], $condition);
        $this->assertStringStartsWith('DELETE', $sql);
    }

} 