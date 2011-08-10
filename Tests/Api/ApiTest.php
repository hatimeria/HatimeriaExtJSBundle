<?php
namespace Hatimeria\ExtJSBundle\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Hatimeria\ExtJSBundle\Api\Api;

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
