<?php

namespace Hatimeria\ExtJSBundle\Tests;

use Symfony\Component\Form\Util\PropertyPath;

use Hatimeria\ExtJSBundle\Parameter\ParameterBag;
use Hatimeria\ExtJSBundle\Dumper\Dumper;
use Hatimeria\ExtJSBundle\Util\Camelizer;
use Hatimeria\ExtJSBundle\Tests\Entity;
use Hatimeria\ExtJSBundle\Dumper\Mappings;
use Hatimeria\ExtJSBundle\Response\Response;
use Hatimeria\ExtJSBundle\Exception\ExtJSException;

/**
 * Description of DumperTest
 *
 * @author Michal Wujas
 */
class DumperTest extends \PHPUnit_Framework_TestCase
{
    private $mappings = array(), $dumper;
    
    private function getSecurity($isAdmin = false)
    {
        $security = $this->getMockBuilder('Hatimeria\ExtJSBundle\Tests\Security')
                ->disableOriginalConstructor()->getMock();
        
        if($isAdmin) {
            $security->expects($this->atLeastOnce())->method("isGranted")
                    ->with("ROLE_ADMIN")->will($this->returnValue(true));
            $security->expects($this->atLeastOnce())->method("getToken")
                    ->will($this->returnValue($this->getMock("Symfony\Component\Security\Core\Authentication\Token\TokenInterface")));
        } else {
            $security->expects($this->any())->method("getToken")
                ->will($this->returnValue(null));
        }
        
        return $security;
    }
    
    public function getAdminSecurity()
    {
        return $this->getSecurity(true);
    }
    
    public function addMapping($name, $paths, $group = 'default')
    {
        $class = "Hatimeria\ExtJSBundle\Tests\\".$name;
        
        if(!isset($this->mappings[$class])) {
            $this->mappings[$class] = array('fields' => array());
        }
        
        $this->mappings[$class]['fields'][$group] = $paths;
    }
    
    private function getDumper()
    {
        if($this->dumper === null) {
            $this->dumper = new Dumper($this->getSecurity(), new Camelizer(), new Mappings($this->mappings));
        }
        
        return $this->dumper;
    }
    
    private function checkDump($resource, $key, $value)
    {
        $d = $this->getDumper();
        $dumped = $d->dump($resource);
        $this->assertTrue($dumped instanceof Response);
        $result = $dumped->toArray();
        $this->assertEquals($value, $result[$key]);
    }
    
    public function testObject()
    {
        $this->addMapping('Entity', array('name','child','created_at','enabled','child.name'));
        $this->addMapping('EntityChild', array('name'));
        
        $e = new Entity('Foo');
        $this->checkDump($e, 'record', array(
            'name'=>'Foo',  
            'enabled' => true, 
            'created_at' => '2011-01-01', 
            'child.name' => 'Bar',
            'child' => array(
                'name'=>'Bar'
                )));
    }
    
    public function testAdmin()
    {
        $this->addMapping('Entity', array('name'));
        $this->addMapping('Entity', array('enabled'), 'admin');
        
        $e = new Entity("Foo");
        
        $this->dumper = new Dumper($this->getAdminSecurity(), new Camelizer(), new Mappings($this->mappings));
        $this->checkDump($e, 'record', array('name' => "Foo", 'enabled' => true));
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
    
    public function testMappingNotFound()
    {
        try {
            $this->checkDump($this->getSecurity(), 'foo', 'bar');
            $this->fail();
        } catch (ExtJSException $e) {
            
        }
    }
}