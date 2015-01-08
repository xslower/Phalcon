<?php
/**
 * @name ServiceTask.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-17
 * @version 0.01
 */
use GreenTea\DI\Cli as DI;
use GreenTea\DI\Services;

class ServiceTask extends \Phalcon\CLI\Task{
    /**
     * @return \GreenTea\Model\NoSql
     */
    protected function getCacheDriver(){
        return DI::getDefault()->getShared(Services::SERVICE_DB_CACHE);
    }

    protected function getHttpClient(){
        return DI::getDefault()->getShared(Services::SERVICE_HTTP_CLIENT);
    }
} 