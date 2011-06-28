<?php

namespace Hatimeria\ExtJSBundle\Router;

class Request
{
    /**
     * The Symfony request object taked by DirectBundle controller.
     * 
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;
    
    /**
     * The HTTP_RAW_POST_DATA if the Direct call is a batch call.
     * 
     * @var JSON
     */
    protected $rawPost;
    
    /**
     * The $_POST data if the Direct Call is a form call.
     * 
     * @var array
     */
    protected $post;

    /**
     * Store the Direct Call type. Where values in ('form','batch').
     * 
     * @var string
     */
    protected $callType;

    /**
     * Store the Direct calls. Only 1 if it a form call or 1.* if it a
     * batch call.
     * 
     * @var array
     */
    protected $calls = null;

    /**
     * Store the $_FILES if it a form call.
     * 
     * @var array
     */
    protected $files;

    /**
     * Initialize the object.
     * 
     * @param Symfony\Component\HttpFoundation\Request $request
     */
    public function __construct($request)
    {        
        // store the symfony request object
        $this->request = $request;
        $this->rawPost = isset($GLOBALS['HTTP_RAW_POST_DATA']) ?  $GLOBALS['HTTP_RAW_POST_DATA'] : array();
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->callType = !empty ($_POST) ? 'form' : 'batch';
    }

    /**
     * Return the type of Direct call.
     *
     * @return string
     */
    public function getCallType()
    {
        return $this->callType;
    }

    public function isXmlHttpRequest()
    {
        return $this->request->isXmlHttpRequest();
    }

    /**
     * Returns true if request is form call type
     *
     * @return bool
     */
    public function isFormCallType()
    {
        return 'form' == $this->getCallType();
    }

    /**
     * Returns true if request is batch call type
     *
     * @return bool
     */
    public function isBatchCallType()
    {
        return 'batch' == $this->getCallType();
    }

    /**
     * Return the files from call.
     * 
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
    
    /**
     * Get the direct calls object.
     *
     * @return array
     */
    public function getCalls()
    {
        if (null == $this->calls) {
            $this->calls = $this->extractCalls();
        }

        return $this->calls;
    }

    /**
     * Extract the ExtDirect calls from request.
     *
     * @return array
     */
    public function extractCalls()
    {
        $calls = array();

        if ('form' == $this->callType) {
            $calls[] = new Call($this->post, 'form');
        } else {
            $decoded = json_decode($this->rawPost);
            $decoded = !is_array($decoded) ? array($decoded) : $decoded;
            
            array_walk_recursive($decoded, array($this, 'parseRawToArray'));

            foreach ($decoded as $call) {
                $calls[] = new Call((array)$call, 'single');
            }
        }
        
        return $calls;
    }

    /**
     * Parse a raw http post to a php array.
     * 
     * @param mixed  $value
     * @param string $key
     */
    private function parseRawToArray(&$value, &$key)
    {
        // parse a json string to an array
        if (is_string($value)) {
            $json = json_decode($value,true);
            
            if ($json) {
                $value = $json;
            }
        }

        // if the value is an object, parse it to an array
        if (is_object($value)) {
            $value = (array)$value;
        }

        // call the recursive function to all keys of array
        if (is_array($value)) {
            array_walk_recursive($value, array($this, 'parseRawToArray'));
        }
  }
}
