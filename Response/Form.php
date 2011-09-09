<?php

namespace Hatimeria\ExtJSBundle\Response;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Form as SymfonyForm;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Response representation for symfony processed form
 * 
 * Contains error list if form is not valid or Success
 * 
 * @author Michal Wujas
 */
class Form extends Validation
{
    /**
     * Form instance
     *
     * @var Form
     */
    private $forms;
    /**
     * @var array
     */
    private $extraErrors = array();

    /**
     * New error list
     *
     * @param mixed $mixed form or errors array
     */
    public function __construct($forms)
    {
        if (!is_array($forms)) {
            $forms = array($forms);
        }
        
        $this->forms = $forms;
    }
    
    /**
     * List of errors
     *
     * @return array field -> message
     */
    public function getFormatted($errors = null)
    {
        // @todo children recursion
        $list = array();

        foreach ($this->forms as $form) {
            /* @var \Symfony\Component\Form\Form $form */
            foreach($form->getChildren() as $field) {
                if (!$field->hasErrors()) continue;
                $messages = array();
                foreach($field->getErrors() as $error) {
                    $messages[] = $error->getMessageTemplate();
                }

                $list[$field->getName()] = $messages;
            }
        }
        $list = array_merge($list, $this->extraErrors);

        return $list;
    }    

    /**
     * Additional errors 
     *
     * @param array $v
     * @return void
     */
    public function setExtraErrors(array $v)
    {
        $this->extraErrors = $v;
    }

    /**
     * Add single additional error to extra errors
     * 
     * @param string $key
     * @param string $value
     * @return void
     */
    public function addExtraError($key, $value)
    {
        $this->extraErrors[$key] = $value;
    }
    /**
     * Is form valid ?
     *
     * @return bool
     */
    public function isValid()
    {
        $valid = true;

        foreach ($this->forms as $form) {
            /* @var \Symfony\Component\Form\Form $form */
            $valid = $valid && $form->isValid();
        }
        
        $valid = $valid && empty($this->extraErrors);
        
        return $valid;
    }
}