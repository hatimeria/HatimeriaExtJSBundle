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
        return lcfirst(preg_replace('/(^|_|-)+(.)/e', "strtoupper('\\2')", $key));
    }
}