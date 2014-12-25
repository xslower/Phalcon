<?php
/**
 * @name Tools.php
 * @desc
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-1
 * @version 0.01
 */

namespace GreenTea\Utility;


use GreenTea\DI\Services;
use Phalcon\DI;

class Tools {
    /**
     * @param $key
     * @return mixed
     */
    public static function cacheGet($key){
        $lc = DI::getDefault()->getShared(Services::SERVICE_LOCALCACHE);
        return $lc->get($key);
    }

    /**
     * @param $key
     * @param $value
     * @param null $lifetime
     * @return mixed
     */
    public static function cacheSet($key, $value, $lifetime = null){
        $lc = DI::getDefault()->getShared(Services::SERVICE_LOCALCACHE);
        return $lc->set($key, $value, $lifetime);
    }

    /**
     * call_user_func_array的扩展封装，支持非静态调用
     * @param callback $callback     如果是数组并且三个元素则默认为非静态调用
     * @param array $params       参数
     * @return mixed
     */
    public static function callUserFuncArray($callback, Array $params){
        if(is_array($callback) && count($callback) === 3 && $callback[2] != 'static'){
            if(!class_exists($callback[0])){
                return false;
            }
            $c = new $callback[0];
            $new_callback = array($c, $callback[1]);
        }else{
            $new_callback = $callback;
        }
        return call_user_func_array($new_callback, $params);
    }

    /**
     * 把字符串中的占位符{{abc}}替换为$input数组中key为abc的值，如果input中没有的key则不替换。
     * @param string $string    待替换数据。如果stack为数组则直接用array_merge即可。
     * @param array  $input
     * @param array  $options
     * @return array
     */
    public static function replacePlaceholder($string, Array $input, Array $options = array()){
        $delimiter_start = XArray::fetchItem($options, 'delimiter_start', '{{');
        $delimiter_end = XArray::fetchItem($options, 'delimiter_end', '}}');

        if(!is_string($string)){
            return $string;
        }
        $offset = 0;
        while(($start = strpos($string, $delimiter_start, $offset)) !== false){
            $end = strpos($string, $delimiter_end, $offset);
            if($end === false){
                break;
            }
            if($end <= $start) break;
            $offset = $end + strlen($delimiter_end) - 1;
            $key = substr($string, $start + strlen($delimiter_start),
                $end - $start - strlen($delimiter_end));

            if(isset($input[$key]) && !empty($input[$key])){
                $holder = $delimiter_start . $key . $delimiter_end;
                $string = str_replace($holder, $input[$key], $string);
                //替换值之后string的长度发生改变，必须对offset也做相应的修改，不然会发生offset溢出
                $s = strlen($holder) - strlen($input[$key]);
                $offset -= $s;
            }
        }
        return $string;
    }

    public static function getRandom($upper = 100){
        $rand = microtime(true) % $upper;
        return $rand + 1;
    }

    /**
     * @param mixed $json
     * @return mixed
     */
    public static function jsonDecode($json){
        if(is_array($json)){
            return $json;
        }
        $decoded  = json_decode( $json, true );
        //检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
        return ($decoded === null) ? $json : $decoded;
    }

    public static function getMimetype($ext) {
        $mime_types = require ROOT_PATH . '/app/config/header.php';
        return XArray::fetchItem($mime_types, $ext, 'application/octet-stream');
    }

    /**
     * 返回当前url
     * @return string
     */
    public static function getCurrentUrl(){
        $protocol = 'http://';
        switch ($_SERVER['SERVER_PORT']){
            case 443: $protocol = 'https://';
        }
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

} 