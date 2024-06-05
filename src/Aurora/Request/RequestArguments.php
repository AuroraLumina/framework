<?php

namespace AuroraLumina\Request;

class RequestArguments
{
    private $data = array();

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __get($key)
    {
        if (isset($this->data[$key]))
        {
            return $this->data[$key];
        }
        else
        {
            return null;
        }
    }

    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    public function count()
    {
        return count($this->data);
    }

    public function keys() 
    {
        return array_keys($this->data);
    }

    public function values()
    {
        return array_values($this->data);
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset) 
    {
        unset($this->data[$offset]);
    }
}