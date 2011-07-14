<?php

namespace Hatimeria\ExtJSBundle\Validation;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

/**
 * Response from validation errors
 */
class ValidationResponse
{
    private $errors;
    
    /**
     * New error list
     *
     * @param mixed $mixed form or errors array
     */
    public function __construct($errors)
    {
        $this->errors = $errors;
    }
    
    public function getFormatted($errors = null)
    {
        if (null === $errors) {
            $errors = $this->errors;
        }
        
        $list = array();
        
        foreach ($errors as $key => $error) {
            if (is_array($error)) {
                $list[$key] = $this->getFormatted($error);
                continue;
            }
            if ($error instanceof ConstraintViolationList) {
                $list[$key] = $this->getFormatted($error);
                continue;
            }
            
            $property = $this->getProperty($error);
            if (!isset($list[$property])) {
                $list[$property] = array();
            }

            $list[$property][] = $error->getMessage();
        }
        
        return $list;
    }
    
    public function getProperty($error)
    {
        $property = $error->getPropertyPath();

        if ($property) {
            return $property;
        }
        
        // constraint zwraca inaczej niz property validator
        $parameters = $error->getMessageParameters();
        
        if(isset($parameters['property'])) {
            return $parameters['property'];
        } else {
            return 'global';
        }
    }
    
    public function isValid()
    {
        return count($this->errors) === 0;
    }
    
    public function getContent()
    {
        $msg     = null;
        $isValid = $this->isValid();
        
        if(!$isValid) {
            $msg = $this->getFormatted($this->errors);
        }
        
    	return array('success' => $isValid, 'msg' => $msg);
    }
    
    public function toArray()
    {
        return $this->getContent();
    }
}