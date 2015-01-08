<?php
/**
 * @name Verification.php
 * @desc 
 * @author 宋文峰(songwenfeng@baidu.com)
 * @date 13-5-28
 * @version 0.01
 */
namespace GreenTea\Utility;

class Verification {

    public static function isPhoto($file){
        $photo_list = array('jpg', 'png', 'gif');
        $ext = strtolower(self::getExtension($file));
        if(!in_array($ext, $photo_list)){
            return false;
        }else{
            return true;
        }
    }

    public static function getExtension($file){
        $ext = '';
        if(($pos = strrpos($file, '.')) !== false){
            $ext = substr($file, $pos + 1);
        }
        return $ext;
    }

    public static function isValid($string){

    }

    public static function isHtml($content){
        $isHtml = preg_match('/<.*?>/', $content);
        return $isHtml;
    }



}