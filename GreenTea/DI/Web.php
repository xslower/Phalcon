<?php
/**
 * @name Web.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-17
 * @version 0.01
 */

namespace GreenTea\DI;


use GreenTea\Model\NoSql;
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
                $handler = XArray::fetchItem($session_config, 'handler', 'redis');
                $session = \GreenTea\Session\Factory::getHandler($handler, $session_config);
                //$session = new \GreenTea\Session\Adapter\Memcached($session_config);
                $session->start();
                return $session;
            });
        }

        //add 404 page for router
        $this->set(Services::SERVICE_ROUTER, function(){
            $router = new \Phalcon\Mvc\Router();
            $router->notFound(['controller' => 'index', 'action' => 'notFound']);
            $router->add('/', ['controller' => 'index', 'action' => 'index']);
            //$router->add('/:controller/:params', ['controller' => 1, 'action' => 'dispatch', 'params' => 2]);
            return $router;
        });

        $url_config = XArray::fetchItem($config, 'url');
        $this->set('url', function() use($url_config){
            $url = new \Phalcon\Mvc\Url();
            $url->setBaseUri($url_config['base_url']);
            return $url;
        });

        $this->set(Services::SERVICE_DISPATCHER, function(){
            $eventsManager = $this->getShared(Services::SERVICE_EVENTMANAGER);
            $eventsManager->attach('dispatch', new \DispatcherPlugin());
            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            $dispatcher->setDI($this);
            $dispatcher->setEventsManager($eventsManager);
            return $dispatcher;
        });

        $this->set(Services::SERVICE_CACHE, function(){
            $cache = new NoSql();
            return $cache;
        });
    }
} 