<?php

namespace Hatimeria\ExtJSBundle\Parameter;

use Symfony\Component\HttpFoundation\ParameterBag as BaseParameterBag;

/**
 * ExtJS Single Call parameters
 *
 * @author Michal Wujas
 */
class ParameterBag extends BaseParameterBag implements \ArrayAccess
{

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->remove($offset, $value);
    }
    
    public function setDefault($key, $value)
    {
        if (!$this->has($key)) {
            $this->set($key, 'x');
        }
    }
}