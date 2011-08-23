<?php

namespace Hatimeria\ExtJSBundle\Tests;

/**
 * Description of Security
 *
 * @author michal
 */
class Security
{
    public function isGranted($role, $object = null)
    {
        return false;
    }
    
    public function getToken()
    {
        return null;
    }
}