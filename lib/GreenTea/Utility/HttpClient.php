<?php
/**
 * @name HttpClient.php
 * @desc curl的封装
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-4
 * @version 0.01
 * TODO 不支持put等soap协议行为
 */

namespace GreenTea\Utility;


class HttpClient {
    const DEFAULT_UA = 'Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1;)';
    const DEFAULT_TIMEOUT = 30;
    //const

    private $_ch;

    public function __construct(){
        $this->_ch = curl_init();
        $options[CURLOPT_USERAGENT] = self::DEFAULT_UA;//UA
        curl_setopt($this->_ch, CURLOPT_TIMEOUT, self::DEFAULT_TIMEOUT);//超时
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->_ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($this->_ch, CURLOPT_HEADER, false);//返回Header
        curl_setopt($this->_ch, CURLOPT_NOBODY, false);//不需要内容
        $options[CURLOPT_MAXCONNECTS] = 10;
        curl_setopt_array($this->_ch, $options);
        //$array [CURLOPT_HTTPHEADER] = array("Content-Type: application/json;charset=UTF-8");
    }

    public function setOptions(Array $options){
        curl_setopt_array($this->_ch, $options);
    }

    public function __destruct(){
        curl_close($this->_ch);
    }

    public function setProxy($proxy){
        //curl_setopt($this->ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
        //curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);//HTTP代理
        //curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);//Socks5代理
        curl_setopt($this->_ch, CURLOPT_PROXY, $proxy);
    }

    public function setReferer($ref){
        curl_setopt($this->_ch, CURLOPT_REFERER, $ref);//Referrer

    }

    public function setCookie($cookie){
        curl_setopt($this->_ch, CURLOPT_COOKIE, $cookie);//Cookie
    }

    public function setCookieFile($file){
        //判断是否有cookie,有的话直接使用,文件为url
        $cookie_jar = '/tmp/cookies/' . $file;
        if (file_exists($cookie_jar)) {
            $options[CURLOPT_COOKIEFILE] = $cookie_jar;
        }
        $options[CURLOPT_COOKIEJAR] = $cookie_jar; //写入cookie信息
        curl_setopt_array($this->_ch, $options);
    }

    public function Get($url){
        return $this->request($url);
    }

    public function Post($url, Array $data){
        return $this->request($url, $data);
    }
    /**
     * curl 请求
     * 如果上传文件，则传参形式举例为：
     * vars = array(
     * 	  'userfile' => '@' . $phptest_data_root . "/pics/1.jpg" . ";type=image/jpeg",
     *    //  cURL的bug，有时会缺失Content-Type，这时需要添加;type=image/xxx
     * )
     */
    public function request($url, Array $data = null) {
        $options[CURLOPT_URL] = $url;

        if ($data) {
            $postfields = http_build_query( $data );
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $postfields;
        }

        if(substr($url, 0, 5) === 'https'){
            $options[CURLOPT_SSL_VERIFYHOST] = false;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }


        curl_setopt_array ( $this->_ch, $options ); //传入curl参数
        $content = curl_exec ( $this->_ch ); //执行

        return $content; //返回
    }


}
