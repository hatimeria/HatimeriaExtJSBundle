<?php
namespace Neton\DirectBundle\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Neton\DirectBundle\Api\Api;

/**
 * Test class of ExtDirect Api.
 *
 * @author Otavio Fernandes <otavio@neton.com.br>
 */
class ApiTest extends WebTestCase
{
    /**
     * Test Api->__toString() method.
     */
    public function test__toString()
    {
        $client = $this->createClient();
        $api = new Api($client->getContainer());
        
        $this->assertRegExp('/Actions/', $api->__toString());
    }
}
