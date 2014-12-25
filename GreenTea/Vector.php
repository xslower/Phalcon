<?php
/**
 * @name Vector.php
 * @desc 跟Phalcon的Registry基本相同，只是那个是final，这个是abstract。
 *  都是对一个数组的封装，如果单独使用为什么不直接使用数组。
 *  只有想要对数组进行功能扩展时才有价值，所以这里强制继承才能使用。
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-22
 * @version 0.01
 * TODO 发现SPL里有个ArrayObject和ArrayIterator，考虑Vector继承它们
 */

namespace GreenTea;


use GreenTea\Utility\XArray;

abstract class Vector implements \ArrayAccess, \Iterator, \Countable, \Serializable, \JsonSerializable {
    protected $_properties = [];

    public function init(Array $array){
        $this->_properties = $array;
    }

    public function merge(Array $append){
        array_replace($this->_properties, $append);
    }

    public function offsetExists($offset){
        return isset($this->_properties[$offset]);
    }

    public function offsetGet($offset){
        return XArray::fetchItem($this->_properties, $offset, null);
    }

    public function offsetSet($offset, $value){
        $this->_properties[$offset] = $value;
    }

    public function offsetUnset($offset){
        unset($this->_properties[$offset]);
    }
    public function current(){
        return current($this->_properties);
    }

    public function next(){
        return next($this->_properties);
    }

    public function key(){
        return key($this->_properties);
    }

    public function valid(){
        return key($this->_properties) === null ? false : true;
    }

    public function rewind(){
        return reset($this->_properties);
    }

    public function count(){
        return count($this->_properties);
    }

    public function serialize(){
        return serialize($this->_properties);
    }

    public function unserialize($serialized){
        $this->_properties = unserialize($serialized);
    }

    public function jsonSerialize(){
        return json_encode($this->_properties);
    }

    public function toArray(){
        return $this->_properties;
    }

    public function __set($key, $value){
        $this->_properties[$key] = $value;
    }

    public function __get($key){
        return XArray::fetchItem($this->_properties, $key);
    }

    public function push($item){
        array_push($this->_properties, $item);
    }

    public function pop(){
        return array_pop($this->_properties);
    }
} 