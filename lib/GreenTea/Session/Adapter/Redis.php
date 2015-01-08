<?php

namespace GreenTea\Session\Adapter;



class Redis extends \Phalcon\Session\Adapter{
    public function __construct(Array $options){
        $path = \GreenTea\Utility\XArray::fetchItem($options, 'path');
        if (!$path) {
            throw new \Exception("The parameter 'path' is required");
        }

        ini_set('session.save_handler', 'redis');
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

        parent::__construct($options);
    }
}
