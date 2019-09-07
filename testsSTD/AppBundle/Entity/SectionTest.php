<?php

/*
 * This file is part of Components of Academ project
 * By contributor S@int-Cyr MAPOUKA
 * (c) Tinzapa <mapoukacyr@yahoo.fr>
 * For the full copyrght and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Tests\AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SectionTest extends WebTestCase
{
    private $em;
    private $application;
    private $utils;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        
        $this->application = new Application(static::$kernel);
        $this->em = $this->application->getKernel()->getContainer()->get('doctrine.orm.entity_manager');
        $this->utils = $this->application->getKernel()->getContainer()->get('app.utils');
    }
    
    /*
     * this is valid for fixture LTB1.yml
     */
    public function testGetStudentNumber()
    {
        //Get the section from the fixture
        $section = $this->em->getRepository('AppBundle:Section')->find(1);
        $this->assertEquals($section->getName(), 'Sec 3eme 1');
        //Test the methode
        $this->assertEquals($section->getStudentNumber(), 2);
    }
    
    
    public function testGetTotalCoefficient()
    {
        //Get the section from the fixture
        $section = $this->em->getRepository('AppBundle:Section')->find(1);
        $this->assertEquals($section->getName(), 'Sec 3eme 1');
        //Test the methode
        $this->assertEquals($section->getTotalCoefficient(), 6);
        
        //Get the section from the fixture
        $section2 = $this->em->getRepository('AppBundle:Section')->find(6);
        $this->assertEquals($section2->getName(), 'Sec Tle C1');
        //Test the methode
        $this->assertEquals($section2->getTotalCoefficient(), 12);
    }
}

