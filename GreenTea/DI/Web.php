<?php
/**
 * @name Web.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-17
 * @version 0.01
 */

namespace GreenTea\DI;


use GreenTea\Utility\XArray;
use Phalcon\DI\FactoryDefault;

class Web extends FactoryDefault{
    public function __construct($config){
        parent::__construct();
        $config['log_path'] = XArray::fetchItem($config, 'log_dir', '/tmp/php/log') . '/web/';
        Services::register($this, $config);

        $view_path = XArray::fetchItem($config, 'view');
        if ($view_path) {
            $this->set('view', function () use ($view_path) {
                $view = new \Phalcon\Mvc\View();
                $view->setViewsDir($view_path);
                return $view;
            });
        }

        $session_config = XArray::fetchItem($config, 'session');
        if($session_config){
            $this->set(Services::SERVICE_SESSION, function() use($session_config){
                $session = new \Phalcon\Session\Adapter\Redis($session_config);
                $session->start();
                return $session;
            });
        }

        //add 404 page for router
        $this->set(Services::SERVICE_ROUTER, function(){
            $router = new \Phalcon\Mvc\Router();
            $router->notFound(array('controller' => 'index', 'action' => 'notFound'));
            $router->add('/', array('controller' => 'index', 'action' => 'index'));
            return $router;
        });

        $url_config = XArray::fetchItem($config, 'url');
        $this->set('url', function() use($url_config){
            $url = new \Phalcon\Mvc\Url();
            $url->setBaseUri($url_config['base_url']);
            return $url;
        });

        $this->set(Services::SERVICE_DISPATCHER, function(){
            $eventsManager = $this->getShared('eventsManager');
            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            $dispatcher->setEventsManager($eventsManager);
            return $dispatcher;
        });
    }
} 