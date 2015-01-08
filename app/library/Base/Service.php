<?php
/**
 * @name Service.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/12/30 0030
 * @version 0.01
 */

namespace Library\Base;


use GreenTea\DI\Services;
use GreenTea\DI\Web as DI;

abstract class Service {

    protected $_di;
    /**
     * @var \Phalcon\Logger\Adapter
     */
    protected $_logger;
    /**
     * @var \Greentea\Model\Nosql
     */
    protected $_cache;

    public function __construct(){
        $this->_di = DI::getDefault();
        $this->_logger = $this->_di[Services::SERVICE_LOGGER];
        $this->_cache = $this->_di[Services::SERVICE_CACHE];
    }


} 