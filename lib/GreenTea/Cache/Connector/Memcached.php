<?php
/**
 * @name Memcached.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-23
 * @version 0.01
 */

namespace GreenTea\Cache\Connector;


use GreenTea\Cache\Connector;
use GreenTea\Utility\XArray;

class Memcached extends Connector{
    const PERSISTENT = 'persistent';
    const PERSISTENT_ID = 'persistent_id';
    const RESET_SERVER = 'reset_server';
    const PERSISTENT_ID_DEFAULT = 'LibMemcached-001';

    public function connect(Array $options){
        if(isset($options['servers']) && is_array($options['servers'])){ //multi server mode
            $config = XArray::fetchItem($options, 'config', []);
            $servers = $options['servers'];
            $mem_options = [
                \Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
                \Memcached::OPT_HASH => \Memcached::HASH_MD5,
                \Memcached::OPT_HASH_WITH_PREFIX_KEY => true,
                \Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
                \Memcached::OPT_REMOVE_FAILED_SERVERS => true, //值存疑
            ];
        }else{ //single server mode
            $default = ['host' => '127.0.0.1', 'port' => 11211];
            $servers = [array_replace($default, $options)];
            $config = $options;
            $mem_options = [];
        }
        $count = count($servers);
        $persistent_id = XArray::fetchItem($config, self::PERSISTENT_ID);
        $persistent = XArray::fetchItem($config, self::PERSISTENT);
        if($count === 0){
            throw new \Exception('No Server to connect');
        }elseif($count === 1){
            $server = current($servers);
            if(!$persistent_id && $persistent){
                $persistent_id = $server['host'] . $server['port'];
            }
        }else{
            if(!$persistent_id && $persistent){
                $persistent_id = self::PERSISTENT_ID_DEFAULT;
            }
        }
        $user = XArray::fetchItem($config, 'user');
        $password = XArray::fetchItem($config, 'password');

        $mch = null;
        if($persistent_id)
            $mch = new \Memcached($persistent_id);
        else
            $mch = new \Memcached();
        $reset_server = XArray::fetchItem($config, self::RESET_SERVER, false);
        if(!$mch->getServerList() || $reset_server){
            $mch->resetServerList();
            if(!$mch->addServers($servers)){
                throw new \Exception('Cannot connect to Memcached server');
            }
            if($user && $password){
                $mch->setSaslAuthData($user, $password);
            }
            foreach ($mem_options as $k => $v) {
                $mch->setOption($k, $v);
            }
        }
        return $mch;
    }
} 