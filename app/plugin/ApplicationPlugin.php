<?php
/**
 * @name ApplicationPlugin.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/12/26 0026
 * @version 0.01
 */

class ApplicationPlugin {
    public function beforeStartModule($event, $module_name){
        echo 'in beforeStartModule';
    }
    public function afterStartModule($event, $module_name){
        echo 'in afterStartModule';
    }

    public function beforeHandleRequest($event, $dispatcher){
        echo 'in beforeHandleRequest';
        return true;
    }
} 