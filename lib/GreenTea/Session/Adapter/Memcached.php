<?php

namespace GreenTea\Session\Adapter;

use GreenTea\Utility\XArray;

class Memcached extends \Phalcon\Session\Adapter{
    public function __construct(Array $options){
        $path = \GreenTea\Utility\XArray::fetchItem($options, 'path');
        if (!$path) {
            throw new \Exception("The parameter 'path' is required");
        }

        ini_set('session.save_handler', 'memcached');
        ini_set('session.save_path', $path);

        if (isset($options['lifetime'])) {
            ini_set('session.gc_maxlifetime', $options['lifetime']);
        }

        if (isset($options['name'])) {
            ini_set('session.name', $options['name']);
        }

        if (isset($options['cookie_lifetime'])) {
            ini_set('session.cookie_lifetime', $options['cookie_lifetime']);
        }
        
        $user = \GreenTea\Utility\XArray::fetchItem($options, 'user');
        $pass = XArray::fetchItem($options, 'password');
        if($user && $pass){
            ini_set('memcached.use_sasl', 'On');
            ini_set('memcached.sess_binary', 'On');
            ini_set('memcached.sess_sasl_username', $user);
            ini_set('memcached.sess_sasl_password', $pass);
            ini_set('memcached.sess_locking', 'Off');
        }
        parent::__construct($options);
    }
}
