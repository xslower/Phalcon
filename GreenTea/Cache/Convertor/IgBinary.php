<?php
/**
 * @name IgBinary.php
 * @desc 据说比json性能高一点点，而且空间占用更少
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-23
 * @version 0.01
 */

namespace GreenTea\Cache\Convertor;


use GreenTea\Cache\Convertor;

class IgBinary extends Convertor{
    public function beforeStore($data){
        return igbinary_serialize($data);
    }
    public function afterRetrieve($data){
        return igbinary_unserialize($data);
    }
} 