<?php
namespace Base\Annotation;

class Annotation
{
    protected $data = array();

    public function __construct($args = array())
    {
        $this->data = $args;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function get($key, $default = null)
    {
        if (empty($this->data[$key])) {
            return $default;
        }

        return $this->data[$key];
    }

    public function exists($key)
    {
        return !empty($this->data[$key]);
    }
}