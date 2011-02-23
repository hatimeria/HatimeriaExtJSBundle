<?php
namespace Neton\DirectBundle\Router;

/**
 * Request encapsule the ExtDirect request call.
 *
 * @author Otavio Fernandes <otavio@neton.com.br>
 */
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

            foreach ($decoded as $call) {
                $calls[] = new Call((array)$call, 'single');
            }
        }
        
        return $calls;
    }
}
