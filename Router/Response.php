<?php
namespace Neton\DirectBundle\Router;

use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * Response encapsule the ExtDirect response to Direct call.
 *
 * @author Otavio Fernandes <otavio@neton.com.br>
 */
class Response extends BaseResponse
{
    /**
     * Call type to respond. Where values in ('form','single).
     *   
     * @var string
     */
    protected $type;

    /**
     * Initialize the object setting it type.
     * 
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Encode the response into a valid json ExtDirect result.
     * 
     * @param  array $result
     * @return string
     */
    public function encode($result)
    {
        return json_encode($result);
    }
}
