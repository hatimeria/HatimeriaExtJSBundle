<?php

namespace Hatimeria\ExtJSBundle\Response;

/**
 * Direct Fail Message
 *
 * @author Michal Wujas
 */
class Failure
{
    /**
     * Content in direct format
     *
     * @var array
     */
    private $content = array('succes' => false);
    
    /**
     * Constructor
     * 
     * @param string $msg  Success message ("Entity saving failed")
     */    
    public function __construct($msg)
    {
        $this->content['msg'] = $msg;
    }
    
    public function toArray()
    {
        return $this->content;
    }
}