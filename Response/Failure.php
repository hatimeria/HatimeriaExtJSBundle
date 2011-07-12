<?php

namespace Hatimeria\ExtJSBundle\Response;

/**
 * Direct Fail Messagee
 *
 * @author Michal Wujas
 */
class Failure
{
    private $content;
    
    public function __construct($msg)
    {
        $this->content = array('succes' => false, 'msg' => $msg );
    }
    
    public function toArray()
    {
        return $this->content;
    }
}