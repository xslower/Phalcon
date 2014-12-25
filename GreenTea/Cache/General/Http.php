<?php
/**
 * @name Http.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-5
 * @version 0.01
 */

namespace GreenTea\Cache\General;


use GreenTea\Cache\Convertor;
use GreenTea\Utility\HttpClient;
use GreenTea\Utility\XArray;
use GreenTea\Cache\General;

class Http extends General{
    const API_URL = 'api_url';
    private $_http_client;
    private $_api_url = '';

    /**
     * Class constructor.
     *
     * @param \GreenTea\Cache\Convertor $convertor
     * @param  array $options
     * @throws \Exception
     */
    public function __construct(Convertor $convertor, Array $options) {
        parent::__construct($convertor, $options);
        if(!isset($options[self::API_URL])){
            throw new \Exception('Require parameter "api_url"!');
        }
        $this->_api_url = $options['api_url'];
        $this->_http_client = new HttpClient();
    }

    protected function _set($key, $content, $lifetime = null){
        $data['method'] = 'set';
        $data['key'] = $key;
        $data['value'] = $content;
        $data['lifetime'] = $lifetime;
        return $this->_request($data);
    }

    protected function _get($key){
        $data['method'] = 'get';
        $data['key'] = $key;
        return $this->_request($data);
    }

    protected function _delete($key) {
        $data['method'] = 'delete';
        $data['key'] = $key;
        return $this->_request($data);
    }

    protected function _queryKeys($prefix = '') {
        $data['method'] = 'get_keys';
        $data['prefix'] = $prefix;
        return $this->_request($data);
    }

    /**
     * @param string $scope
     * @return mixed
     */
    protected function _flush($scope = self::SCOPE_TABLE)  {
        $data['method'] = 'flush';
        $data['scope'] = $scope;
        return $this->_request($data);
    }

    protected function _multiSet(Array $items, $lifetime = null){
        $data['method'] = 'multi_set';
        $data['items'] = $items;
        $data['lifetime'] = $lifetime;
        $this->_request($data);
    }

    protected function _multiGet(Array $keys){
        $data['method'] = 'multi_get';
        $data['keys'] = $keys;
        return $this->_request($data);
    }

    protected function _multiDel(Array $keys){
        $data['method'] = 'multi_del';
        $data['keys'] = $keys;
        return $this->_request($data);
    }

    protected function _request($data){
        $data['db_name'] = $this->_db_name;
        $data['table_name'] = $this->_table_name;
        return $this->_http_client->Post($this->_api_url, $data);
    }

    /**
     * do nothing
     * @param $dbname
     */
    protected function _switchDb($dbname){
    }

    /**
     * do nothing
     * @param $table
     */
    protected function _switchTable($table){
    }

}
