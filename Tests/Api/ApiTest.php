<?php
namespace Hatimeria\ExtJSBundle\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Hatimeria\ExtJSBundle\Api\Api;

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
        $client->request('GET','/');
        $api    = new Api($client->getContainer(), $client->getRequest());

        $this->assertRegExp('/Actions/', $api->__toString());
    }
}
