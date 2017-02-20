<?php
/**
 * Created by PhpStorm.
 * User: tilotiti
 * Date: 20/02/2017
 * Time: 17:50
 */

namespace Tiloweb\QuipuBundle\Model;


class Contact
{
    protected $id;
    protected $attributes;

    public function __construct()
    {
        $this->attributes = new \stdClass();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function get($key = false) {
        if(!$key) {
            return $this->attributes;
        }

        return isset($this->attributes->{$key}) ? $this->attributes->{$key} : null;
    }

    public function set($key, $value = false) {
        if(!$value && is_object($key)) {
            $this->attributes = $key;
        } else {
            $this->attributes->{$key} = $value;
        }
    }
}