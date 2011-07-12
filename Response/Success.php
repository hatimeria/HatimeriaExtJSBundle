<?php

namespace Hatimeria\ExtJSBundle\Response;

/**
 * Direct Fail Messagee
 *
 * @author Michal Wujas
 */
class Success
{
    private $content;
    
    public function __construct($msg = null)
    {
        $this->content = array('succes' => true);
        if($msg != null) {
            $this->content['msg'] = $msg;
        }
    }
    
    public function toArray()
    {
        return $this->content;
    }
}