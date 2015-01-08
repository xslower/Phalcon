<?php
/**
 * @name Ini.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-22
 * @version 0.01
 */

namespace GreenTea\Config\Format;

use GreenTea\Config\Format;

class Ini extends Format{
    public function load(){
        $config = parse_ini_file($this->_path, true);
        $this->init($config);
    }
    public function save(){
        $ini_string = '';
        $config = $this->toArray();
        foreach ($config as $k => $v) {
        //因为ini只有最前面的才能解析为一维数组，所以把所有一维放到最前面
            if(!is_array($v)){
                $ini_string .= "$k = $v\r\n";
                unset($config[$k]);
            }
        }
        foreach ($config as $sec_name => $section) {
            $ini_string .= "[$sec_name]\r\n";
            foreach ($section as $k => $v) {
                $ini_string .= "$k = $v\r\n";
            }
        }
        return file_put_contents($this->_path, $ini_string);

    }
} 