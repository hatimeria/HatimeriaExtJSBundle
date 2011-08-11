<?php

namespace Hatimeria\ExtJSBundle\Util;

/**
 * Description of Camelizer
 *
 * @author michal
 */
class Camelizer
{
    public function camelize($key)
    {
        return lcfirst(preg_replace('/(^|_|-)+(.)/e', "strtoupper('\\2')", $key));
    }
}