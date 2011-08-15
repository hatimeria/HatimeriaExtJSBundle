<?php

namespace Hatimeria\ExtJSBundle\Response;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Form\FormError;

/**
 * Response from validation errors
 * 
 * @author Michal Wujas
 */
class Validation implements Response
{
    /**
     * Errors
     *
     * @var mixed
     */
    private $errors;
    
    /**
     * New error list
     *
     * @param mixed $errors form or errors array
     */
    public function __construct($errors)
    {
        $this->errors = $errors;
    }
    
    /**
     * List of errors
     *
     * @param array $errors
     * 
     * @return array
     */
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

    /**
     * Validated property name
     *
     * @param Object $error
     * 
     * @return string
     */
    public function getProperty($error)
    {
        $property = $error->getPropertyPath();

        if ($property) {
            return $property;
        }
        
        $parameters = $error->getMessageParameters();
        
        if(isset($parameters['property'])) {
            return $parameters['property'];
        } else {
            return 'global';
        }
    }
    
    /**
     * Validation success or failure
     *
     * @return bool
     */
    public function isValid()
    {
        return count($this->errors) === 0;
    }
    
    /**
     * Validation response
     *
     * @return array
     */
    public function getContent()
    {
        $msg     = null;
        $isValid = $this->isValid();
        
        if(!$isValid) {
            $msg = $this->getFormatted($this->errors);
        }
        
    	return array('success' => $isValid, 'msg' => $msg);
    }
    
    /**
     * Array representation
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->getContent();
    }
}