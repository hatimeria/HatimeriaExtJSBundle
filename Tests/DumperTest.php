<?php

namespace Hatimeria\ExtJSBundle\Tests;

use Symfony\Component\Form\Util\PropertyPath;
use Sfera\ProjectBundle\Entity\Tag;
use Hatimeria\ExtJSBundle\Parameter\ParameterBag;
use Hatimeria\ExtJSBundle\Dumper\Dumper;
use Hatimeria\ExtJSBundle\Util\Camelizer;
use Hatimeria\ExtJSBundle\Tests\Entity;

/**
 * Description of DumperTest
 *
 * @author Michal Wujas
 */
class DumperTest extends \PHPUnit_Framework_TestCase
{
    private $mappings;
    
    private function getSecurity($isAdmin = false)
    {
        $security = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')->disableOriginalConstructor()->getMock();
        
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
    
    public function testDumper()
    {
        $this->addMapping('Entity', array('name','child'));
        $this->addMapping('EntityChild', array('name'));
        
        $e = new Entity('Foo');
        $d = new Dumper($this->getSecurity(), new Camelizer(), $this->mappings);
        $this->assertSame($d->dumpObject($e), array('name'=>'Foo', 'child' => array('name'=>'Bar')));
    }
}