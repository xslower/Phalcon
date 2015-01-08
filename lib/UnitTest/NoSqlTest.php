<?php
/**
 * @name NoSqlTest.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/11/4 0004
 * @version 0.01
 */

use \GreenTea\DI\Cli as DI;

class NoSqlTest extends DistrbuteTest{

    public static function setUpBeforeClass(){
    }

    protected function tryCache(\GreenTea\Model\NoSql $cache){
        $this->assertEquals(null, $cache->get('key1'));
        $cache->set('key1', 'value');
        $this->assertEquals('value', $cache->get('key1'));
        $items = ['k1' => 11, 'k2' => 'str2', 'k3' => [1,2,3]];
        $cache->multiSet($items);
        $result = $cache->multiGet(['k1', 'k2', 'k3']);
        $this->assertEquals(11, $result['k1']);
        $this->assertEquals('str2', $result['k2']);
        $this->assertTrue(is_array($result['k3']));
        $cache->del('k1');
        $this->assertEquals(null, $cache->get('k1'));
    }

    public function testReadWrite(){
        DI::getDefault()->set(\GreenTea\DI\Services::CONFIG_NOSQL, function(){
            return self::$_cache_config;
        });
        //$no = new \GreenTea\Model\NoSql();
        $cache = DI::getDefault()->get(\GreenTea\DI\Services::SERVICE_CACHE);
        $this->tryCache($cache);
    }
} 