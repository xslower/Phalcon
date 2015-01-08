<?php
/**
 * @name ConfigTest.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/11/6 0006
 * @version 0.01
 */

class UtilityTest extends PHPUnit_Framework_TestCase{
    public function __construct(){

    }

    public function testConfig(){
        $path = '/tmp/php/test/config.php';
        $config = new \GreenTea\Config\Format\Php($path);
        $config['aa'] = 'bb';
        $config['cc'] = 'dd';
        $config->save();
        $c = require($path);
        $this->assertTrue(is_array($c));
    }

    public function testHttpClient(){
        $c = new \GreenTea\Utility\HttpClient();
        $d = $c->Get('http://m.taobao.com/');
        $this->assertStringStartsWith('<html>', $d);
        //$e = $c->Post('http://m.taobao.com/login/', ['user' => 'abc', 'password' => 'def']);
    }

    public function testString(){
        $a = 'ab_cd_ef';
        $b = \GreenTea\Utility\String::camelize($a);
        $this->assertEquals('AbCdEf', $b);
    }

    public function testArray(){

    }

    public function testTools(){

    }
} 