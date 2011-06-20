<?php
namespace Hatimeria\ExtJSBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Hatimeria\ExtJSBundle\Controller\DirectController;

class DirectControllerTest extends WebTestCase
{
    /**
     * Test getApi method.
     */
    public function testGetApi()
    {
        // create test env
        $client = $this->createClient();

        $crawler = $client->request('GET', '/api.js');
        
        // test add provider
        $this->assertTrue($crawler->filter('html:contains("Ext.Direct.addProvider(")')->count() > 0);
        
        // test url in direc api
        $this->assertTrue($crawler->filter('html:contains("url")')->count() > 0);

        // test actions in direc api
        // @todo: improve this test
        $this->assertTrue($crawler->filter('html:contains("actions")')->count() > 0);
    }
}
