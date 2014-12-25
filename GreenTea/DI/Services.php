<?php
/**
 * @name Services.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-31
 * @version 0.01
 */

namespace GreenTea\DI;

use GreenTea\Utility\XArray;
use Phalcon\DI\FactoryDefault;

class Services  {
    const FACTORY_MODEL_CONNECTION = 'ConnectionFactory';
    const FACTORY_MODEL_DISTRIBUTE = 'DistributeFactory';
    const FACTORY_APP_MODEL = 'app_model_factory';
    const CONFIG_NOSQL = 'nosql_config';
    const CONFIG_DB = 'db_config';
    const CONFIG_DB_CUSTOM = 'db_custom';
    const SERVICE_DB_CACHE = 'DbCacheService';
    const SERVICE_LOCALCACHE = 'LocalCache';
    const SERVICE_LOGGER = 'log_service';
    const SERVICE_PHALCON_MODEL = 'phalcon_model';
    const SERVICE_DISPATCHER = 'dispatcher';
    const SERVICE_ROUTER = 'router';
    const SERVICE_SESSION = 'session';
    const SERVICE_QUEUE = 'queue';
    const SERVICE_HTTP_CLIENT = 'http_client';

    public static function register(FactoryDefault $di, Array $config){
        //几个工厂
        $di->set(self::FACTORY_MODEL_CONNECTION, '\GreenTea\Model\Connector\Factory');
        $di->set(self::FACTORY_MODEL_DISTRIBUTE, '\GreenTea\Model\Distribute\Factory');
        $di->set(self::FACTORY_APP_MODEL, 'ModelFactory');
        //cache配置
        $nosql_config_path = XArray::fetchItem($config, self::CONFIG_NOSQL);
        if($nosql_config_path && file_exists($nosql_config_path)){
            $di->set(self::CONFIG_NOSQL, function() use($nosql_config_path){
                return require($nosql_config_path);
            });
        }
        //其实是缓存服务，db内、db外都可使用
        $di->set(self::SERVICE_DB_CACHE, function () {
            /**
             * 如果Db的Cache需要使用与NoSQL不同的分发策略，则传入不同的配置文件即可(@详见NoSQL.php)
             * If u want to use different distribute strategy for db's cache and nosql,
             * u can insert different name of config file service to NoSQL.(@detail in NoSQL.php)
             */
            return new \GreenTea\Model\NoSql(self::CONFIG_NOSQL);
        });
        //db配置
        $db_config_path = XArray::fetchItem($config, self::CONFIG_DB);
        if($db_config_path && file_exists($db_config_path)){
            $di->set(self::CONFIG_DB, function() use($db_config_path){
                return require($db_config_path);
            });
        }
        //表与db节点映射配置
        $db_custom_path = XArray::fetchItem($config, self::CONFIG_DB_CUSTOM);
        if($db_custom_path && file_exists($db_custom_path)){
            $di->set(self::CONFIG_DB_CUSTOM, function() use($db_custom_path){
                return require($db_custom_path);
            });
        }

        $di->set(self::SERVICE_PHALCON_MODEL, '\PhalconEx\Mvc\Model');

        //日志
        $log_path = XArray::fetchItem($config, 'log_path', '/tmp/php/log/');
        $di->set(self::SERVICE_LOGGER, function() use($log_path){
            if(!is_dir($log_path)) mkdir($log_path, 0777, true);
            $logger = new \Phalcon\Logger\Adapter\File($log_path . date('Y-m-d') . '.log');
            return $logger;
        });

        //队列
        $queue_config = XArray::fetchItem($config, 'queue');
        if($queue_config){
            $di->set(self::SERVICE_QUEUE, function($queue_name = '') use($queue_config){
                $queue_config['queue_name'] = $queue_name;
                $convertor = new \GreenTea\Cache\Convertor\Json();
                return new \GreenTea\Cache\Queue\Redis($convertor, $queue_config);
            });
        }

        //curl封装
        $di->set(self::SERVICE_HTTP_CLIENT, '\GreenTea\Utility\HttpClient');

        /**
         * 本地缓存注册到通用里，因为：
         * 1. cli使用distribute也是需要缓存的
         */
        $di->set(self::SERVICE_LOCALCACHE, function(){
            if(extension_loaded('apcu')){
                $convertor = new \GreenTea\Cache\Convertor\Json();
                $cache = new \GreenTea\Cache\General\Apcu($convertor, ['lifetime' => 3600]);
            }else{
                $converter = new \GreenTea\Cache\Convertor\Json();
                $cache = new \GreenTea\Cache\General\File($converter, array('cache_dir' => '/tmp/php/cache'));
            }
            return $cache;
        });
    }
} 