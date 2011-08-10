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
        $client = $this->createClient();

        $crawler = $client->request('GET', '/api.js');
        
        $js = $client->getResponse()->getContent();
        
        $this->assertTrue(strpos($js, 'Ext.Direct') === 0);
    }
}
