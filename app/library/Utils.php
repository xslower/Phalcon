<?php
namespace Library;

use GreenTea\Utility\XArray;

/**
 * @name Utils.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2015/1/5 0005
 * @version 0.01
 */

class Utils {

    public static function sendJson($data, \Phalcon\Http\Response $response){
        if(is_array($data) && XArray::isTwoDimension($data)){ //去掉索引，改为js数组
            $data = array_merge([], $data);
        }
        //$this->response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setContentType('application/json', 'utf-8');
        $json = json_encode($data);
        $callback = XArray::fetchItem($_GET, 'callback');
        if($callback){ //如果是jsonp请求则以jsonp格式返回
            $response->setContentType('application/javascript', 'utf-8');
            $jsonp = $callback . '(' . $json . ')';
            $json = $jsonp;
        }
        $response->setContent($json);
        $response->send();
    }
} 