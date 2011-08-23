<?php

namespace Hatimeria\ExtJSBundle\Tests;

use Symfony\Component\Form\Util\PropertyPath;

use Hatimeria\ExtJSBundle\Parameter\ParameterBag;
use Hatimeria\ExtJSBundle\Dumper\Dumper;
use Hatimeria\ExtJSBundle\Util\Camelizer;
use Hatimeria\ExtJSBundle\Tests\Entity;
use Hatimeria\ExtJSBundle\Dumper\Mappings;
use Hatimeria\ExtJSBundle\Response\Response;

/**
 * Description of DumperTest
 *
 * @author Michal Wujas
 */
class DumperTest extends \PHPUnit_Framework_TestCase
{
    private $mappings = array();
    
    private function getSecurity($isAdmin = false)
    {
        $security = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
                ->disableOriginalConstructor()->getMock();
        
        return $security;
    }
    
    public function getAdminSecurity()
    {
        return $this->getSecurity(true);
    }
    
    public function addMapping($class, $paths)
    {
        $this->mappings["Hatimeria\ExtJSBundle\Tests\\".$class] = array('fields' => array('default' => $paths));
    }
    
    private function getDumper()
    {
        return new Dumper($this->getSecurity(), new Camelizer(), new Mappings($this->mappings));
    }
    
    private function checkDump($resource, $key, $value)
    {
        $d = $this->getDumper();
        $dumped = $d->dump($resource);
        $this->assertTrue($dumped instanceof Response);
        $result = $dumped->toArray();
        $this->assertSame($value, $result[$key]);
    }
    
    public function testObject()
    {
        $this->addMapping('Entity', array('name','child'));
        $this->addMapping('EntityChild', array('name'));
        
        $e = new Entity('Foo');
        $this->checkDump($e, 'record', array('name'=>'Foo', 'child' => array('name'=>'Bar')));
    }
    
    public function testPager()
    {
        $this->addMapping('Entity', array('name'));
        
        $pager = $this->getMockBuilder("Hatimeria\ExtJSBundle\Doctrine\Pager", array('getEntities','getCount','getLimit'))
                ->disableOriginalConstructor()->getMock();
        
         $pager->expects($this->atLeastOnce())
              ->method('getEntities')
              ->will($this->returnValue(array(new Entity("Frank"), new Entity("Hatimeria"))));
         
         $pager->expects($this->atLeastOnce())
              ->method('getCount')
              ->will($this->returnValue(2));
        
         $this->checkDump($pager, 'total', 2);
         $this->checkDump($pager, 'records', array(array('name' => 'Frank'), array('name' => 'Hatimeria')));
    }
    
    public function testCollection()
    {
        $this->addMapping('Entity', array('name'));
        $this->checkDump(array(new Entity("Frank")), 'total', 1);
        $this->checkDump(array(new Entity("Frank")), 'records', array(array('name' => 'Frank')));
    }
}