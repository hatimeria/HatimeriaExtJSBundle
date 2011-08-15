<?php

namespace Hatimeria\ExtJSBundle\Validation;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Response representation for symfony processed form
 * 
 * Contains error list if form is not valid or Success
 * 
 * @author Michal Wujas
 */
class FormResponse extends ValidationResponse
{
    /**
     * Form instance
     *
     * @var Form
     */
    private $form;
    
    /**
     * New error list
     *
     * @param mixed $mixed form or errors array
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
    }
    
    /**
     * List of errors
     *
     * @return array field -> message
     */
    public function getFormatted()
    {
        // @todo children recursion
        $list = array();
        
        foreach($this->form->getChildren() as $field) {
            if (!$field->hasErrors()) continue;
            $messages = array();
            foreach($field->getErrors() as $error) {
                $messages[] = $error->getMessageTemplate();
            }
            
            $list[$field->getName()] = $messages;
        }
        
        return $list;
    }    
    
    /**
     * Is form valid ?
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->form->isValid();
    }
}