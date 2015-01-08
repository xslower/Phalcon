<?php
/**
 * @name DbTest.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/11/7 0007
 * @version 0.01
 */


use \GreenTea\DI\Web as DI;
use GreenTea\Model\DataBase\Transaction;

class DbTest extends DistrbuteTest{
    public function __construct(){
        DI::getDefault()->set(\GreenTea\DI\Services::CONFIG_DB, function(){
            return self::$_db_config;
        });
        DI::getDefault()->set(\GreenTea\DI\Services::CONFIG_NOSQL, function(){
            return self::$_cache_config;
        });

    }

    private function testCRUD(){
        $condition['version'] = 123;

        $condition['version']['>'] = 100;
        $condition['version']['<='] = 200;

        $condition['version']['BETWEEN'] = [100, 200];
        $condition['version'][\GreenTea\Model\Sql\Factory::BETWEEN] = [100, 200];

        $condition['version']['IN'] = [1, 2, 3, 4];
        $condition['version'][\GreenTea\Model\Sql\Factory::IN] = [1, 2, 3, 4];

        $condition['version']['LIKE'] = 'something';
        $condition['version'][\GreenTea\Model\Sql\Factory::LIKE] = 'something';

        $append[\GreenTea\Model\Sql\Factory::ORDER_BY] = 'field1';
        $append[\GreenTea\Model\Sql\Factory::GROUP_BY] = 'field2';
        $append[\GreenTea\Model\Sql\Factory::LIMIT] = '10,20';
        $test = new Test();
        $test->getList($condition, $append); //取多条
        $test->getOne($condition); //取单条

        //插入
        $fields['name'] = 'aaaa';
        $fields['sex'] = 'male';
        $fields['count'] = 1;
        $id = $test->insert($fields);

        //修改
        $fields['name'] = 'bbb';
        $fields['count'][\GreenTea\Model\Sql\Factory::SELF_ADD] = 1; //自增
        $condition['id'] = $id;
        $test->update($fields, $condition);

        //删除
        $test->delete($condition);
    }

    public function testCorrelate(){

    }

    /**
     *
     */
    public function testTransaction(){
        $test = new TestModel();
        $comm = new MyTest();
        $tr = new Transaction($test, $comm);
        $tr->begin();
        try{
            $ret[] = $test->insert(['name'=>'UnitTest']);
            $this->assertTrue($ret[0] > 0);
            $ret[] = $comm->insert(['name'=>'UnitTestComm']);
            $this->assertTrue($ret[1] > 0);
        }catch (\Exception $e){
            $tr->rollback();
        }

        $tr->commit();
    }
} 