<?php
namespace Gis;

class GisRecursiveIterator implements \RecursiveIterator{

    public $_data;
    public $_position = 0;

    public function __construct(array $data) {
        $this->_data = $data;
    }

    public function valid() {
        return isset($this->_data[$this->_position]);
    }

    public function hasChildren(){
        if(!is_array($this->_data[$this->_position])){
            return false;
        }
        elseif(!empty($this->_data[$this->_position]['type'])){
            return false;
        }
        else{
            return !empty($this->_data);
        }
    }

    public function next() {
        $this->_position++;
    }

    public function current() {
        return $this->_data[$this->_position];
    }

    public function getChildren() {
        $arr = array_values($this->_data[$this->_position]);
        return new GisRecursiveIterator($arr);
    }

    public function rewind() {
        $this->_position = 0;
    }

    public function key() {
        return $this->_position;
    }
}
