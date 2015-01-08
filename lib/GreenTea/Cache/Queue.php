<?php
/**
 * @name Queue.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-23
 * @version 0.01
 */

namespace GreenTea\Cache;


use GreenTea\Utility\XArray;

abstract class Queue{
    const QUEUE_NAME = 'queue_name';

    protected $_convertor;
    protected $_options;
    protected $_key;

    /**
     * @param Convertor $convertor
     * @param array $options
     */
    public function __construct(Convertor $convertor, Array $options = []){
        $this->_convertor = $convertor;
        $this->_options = $options;
        $this->_key = XArray::fetchItem($options, self::QUEUE_NAME);
    }

    public function push($item, $key = null){
        if($key === null){
            $key = $this->_key;
        }
        $front = $this->getConvertor();
        $this->_push($key, $front->beforeStore($item));
    }
    abstract protected function _push($key, $content);

    public function pop($key = null){
        if($key === null){
            $key = $this->_key;
        }
        $front = $this->getConvertor();
        $item = $this->_pop($key);
        return $front->afterRetrieve($item);
    }
    abstract protected function _pop($key);

    public function length($key = null){
        if($key === null){
            $key = $this->_key;
        }
        return $this->_length($key);
    }
    abstract protected function _length($key);

    public function clear($key = null){
        if($key === null){
            $key = $this->_key;
        }
        $this->_clear($key);
    }
    abstract protected function _clear($key);

    protected function getConvertor(){
        return $this->_convertor;
    }
} 