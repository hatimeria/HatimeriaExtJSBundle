<?php

namespace Hatimeria\ExtJSBundle\Util;

/**
 * Change birthday_at to birthdayAt
 *
 * @author Michal Wujas
 */
class Camelizer
{
    public function camelize($key)
    {
        return lcfirst(preg_replace_callback('/(^|_|-)+(.)/', function($m) { return strtoupper($m[2]); }, $key));
    }
}
