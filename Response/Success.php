<?php

namespace Hatimeria\ExtJSBundle\Response;

use Hatimeria\ExtJSBundle\Parameter\ParameterBag;

/**
 * Direct Success Response
 *
 * @author Michal Wujas
 */
class Success
{
    private $content;
    
    public function __construct($msg = null)
    {
        $content = new ParameterBag();
        $content->set('success', true);
        
        if($msg != null) {
            $content->set('msg', $msg);
        }

        $this->content = $content;
    }
    
    public function toArray()
    {
        return $this->content->all();
    }

    public function set($key, $value)
    {
        $this->content->set($key, $value);
    }

    public function get($key)
    {
        return $this->content->get($key);
    }

    public function has($key)
    {
        return $this->content->has($key);
    }

    public function remove($key)
    {
        $this->content->remove($key);
    }

}