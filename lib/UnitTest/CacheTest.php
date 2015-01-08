<?php
/**
 * @name CacheTest.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/11/5 0005
 * @version 0.01
 */

ini_set('xdebug.show_exception_trace', 0);

class CacheTest extends PHPUnit_Framework_TestCase{
    protected static $_cv;
    protected $_options = [
        'host' => '127.0.0.1',
        'lifetime' => 1800,
    ];

    public static function setUpBeforeClass(){
        self::$_cv = new \GreenTea\Cache\Convertor\Json();
    }

    protected function tryCache(\GreenTea\Cache\General $cache){
        $cache->flush();
        $this->assertEquals(null, $cache->get('key1'));
        $cache->set('key1', 'value');
        $this->assertEquals('value', $cache->get('key1'));
        $items = ['k1' => 11, 'k2' => 'str2', 'k3' => [1,2,3]];
        $cache->multiSet($items);
        $result = $cache->multiGet(['k1', 'k2', 'k3']);
        $this->assertEquals(11, $result['k1']);
        $this->assertEquals('str2', $result['k2']);
        $this->assertTrue(is_array($result['k3']));
        $keys = $cache->queryKeys('k');
        $this->assertTrue(array_search('k2', $keys) !== false);
        $cache->flush();
        $keys = $cache->queryKeys();
        $this->assertTrue(empty($keys));
        $this->assertEquals(null, $cache->get('k2'));
    }

    protected function tryDbTable(\GreenTea\Cache\General $cache){
        $this->tryCache($cache);
        $cache->switchDb('new_db');
        $this->tryCache($cache);
        $cache->switchTable('new_table');
        $this->tryCache($cache);
    }

    /**
     * @comment 命令行模式无法使用apc，所以apc无法测试
     */
    public function testApcu(){
        //$apc = new \GreenTea\Cache\General\Apcu(self::$_cv, ['lifetime' => 1800]);
        //$this->tryCache($apc);
    }

    public function testFile(){
        echo 'File:'."\n";
        $file = new \GreenTea\Cache\General\File(self::$_cv, []);
        $this->tryDbTable($file);

    }

    public function testMemcache(){
        echo 'Memcache:'."\n";
        $mem = new \GreenTea\Cache\General\Memcache(self::$_cv, ['lifetime' => 1800]);
        $sqk = new \GreenTea\Cache\General\SupportQueryKey($mem);
        $mem->flush();
        $this->tryDbTable($sqk);
    }
    public function testMemcached(){
        echo 'Memcached:'."\n";
        $mem = new \GreenTea\Cache\General\Memcached(self::$_cv, ['lifetime' => 1800]);
        $sqk = new \GreenTea\Cache\General\SupportQueryKey($mem);
        $mem->flush();
        $this->tryDbTable($sqk);
    }
    public function testRedis(){
        echo 'Redis:'."\n";
        $redis = new \GreenTea\Cache\General\Redis(self::$_cv, ['lifetime' => 1800]);
        $this->tryDbTable($redis);
    }

    public function testQueue(){
        $qu = new \GreenTea\Cache\Queue\Redis(self::$_cv, [\GreenTea\Cache\Queue::QUEUE_NAME => 'tq']);
        $qu->clear();
        $qu->push([1,2,3,4]);
        $qu->push(['a','b','c' => 'd']);
        $this->assertEquals(2, $qu->length());
        $this->assertArrayHasKey('c', $qu->pop());
        $this->assertEquals(1, $qu->length());
    }
} 