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
use AppBundle\Entity\Teacher;

class UtilsSTDTest extends WebTestCase
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
    public function testComputedMark()
    {
        //Test the avarage of Angela as prepared in fixtures
        $student = $this->em->getRepository('AppBundle:Student')->find(2);
        //Make sure student is GAZAMBETI
        $this->assertEquals($student->getName(), 'GAZAMBETI');
        //Make sure program is Info 1ere G3
        $program = $this->em->getRepository('AppBundle:Program')->find(1);
        $this->assertEquals($program->getName(), 'Prog Info 1ere G3');
        //Get all the marks
        $marks = $student->getMarks();
        //Make sure it is three as in the fixture
        $this->assertEquals(count($marks), 3);
        //Make sure mark values are right as in fixture
        $this->assertEquals($marks[0]->getValue(), 14.5);
        $this->assertEquals($marks[1]->getValue(), 11);
        $this->assertEquals($marks[2]->getValue(), 7.5);
        //test the avarage computing methode now
        $avarage = $this->utils->getComputedMark($student, $program);
        $this->assertEquals($avarage, 12.75);
        //Test the avarage of Student name_1 as prepared in fixtures
        $student2 = $this->em->getRepository('AppBundle:Student')->find(3);
        //Make sure student is Student name_1
        $this->assertEquals($student2->getName(), 'Eleve name_1');
        //Make sure no division by zero
        $avarage2 = $this->utils->getComputedMark($student2, $program);
        $this->assertEquals($student2->getName(), 'Eleve name_1');
        $this->assertEquals($avarage2, 8);
    }
    
    /*
     * This is valid for fixture STD1.yml
     * for this case there have to be discrimination between
     * Devoir & Composition so Utils::getComputedMark(null, null, $allMark=false)
     */
    /*public function testComputedMark2()
    {
        //Test the avarage of YASSI as prepared in fixtures
        $student = $this->em->getRepository('AppBundle:Student')->find(2);
        //Make sure student is YASSI
        $this->assertEquals($student->getName(), 'YASSI');
        //Make sure program is Physique Chimie 3eme
        $program = $this->em->getRepository('AppBundle:Program')->find(1);
        $this->assertEquals($program->getName(), 'Prog Physique Chimie 3eme');
        //Get all the marks
        $marks = $student->getMarks();
        //Make sure it is three as in the fixture
        $this->assertEquals(count($marks), 5);
        //Make sure mark values are right as in fixture
        $this->assertEquals($marks[0]->getValue(), 14.5);
        $this->assertEquals($marks[1]->getValue(), 11);
        $this->assertEquals($marks[2]->getValue(), 13.5);
        //test the avarage computing methode now
        $avarage = $this->utils->getComputedMark($student, $program);
        $this->assertEquals($avarage, 13.00);
        
        //Test the average of KOSSI
        $student2 = $this->em->getRepository('AppBundle:Student')->find(1);
        //Make sure student is KOSSI
        $this->assertEquals($student2->getName(), 'KOSSI');
        //Make sure program is Physique Chimie 3eme
        $program = $this->em->getRepository('AppBundle:Program')->find(1);
        $this->assertEquals($program->getName(), 'Prog Physique Chimie 3eme');
        //Get all the marks
        $marks = $student2->getMarks();
        //Make sure it is three as in the fixture
        $this->assertEquals(count($marks), 5);
        //Make sure mark values are right as in fixture
        $this->assertEquals($marks[0]->getValue(), 12.5);
        $this->assertEquals($marks[1]->getValue(), 8.5);
        $this->assertEquals($marks[2]->getValue(), 9.5);
        //test the avarage computing methode now
        $avarage = $this->utils->getComputedMark($student2, $program, false);
        //Only Devoir is considered here.
        $this->assertEquals($avarage, 10.5);
    }*/
    
    /*public function testGetMarkComposition()
    {
        //Test the average of KOSSI (1ere trimestre)
        $student2 = $this->em->getRepository('AppBundle:Student')->find(1);
        //Make sure student is KOSSI
        $this->assertEquals($student2->getName(), 'KOSSI');
        //Make sure program is Physique Chimie 3eme
        $program = $this->em->getRepository('AppBundle:Program')->find(1);
        $this->assertEquals($program->getName(), 'Prog Physique Chimie 3eme');
        //Make sure it's the right sequence
        $sequence = $this->em->getRepository('AppBundle:Sequence')->find(1);
        $this->assertEquals($sequence->getName(), '1er Trimestre');
        $markForComposition = $this->utils->getMarkForComposition($student2, $program, $sequence);
        $this->assertEquals($markForComposition, 19);
        
        //Test the average of KOSSI (2eme trimestre)
        $student3 = $this->em->getRepository('AppBundle:Student')->find(1);
        //Make sure student is KOSSI
        $this->assertEquals($student3->getName(), 'KOSSI');
        //Make sure program is Physique Chimie 3eme
        $program = $this->em->getRepository('AppBundle:Program')->find(1);
        $this->assertEquals($program->getName(), 'Prog Physique Chimie 3eme');
        //Make sure it's the right sequence
        $sequence2 = $this->em->getRepository('AppBundle:Sequence')->find(2);
        $this->assertEquals($sequence2->getName(), '2em Trimestre');
        $markForComposition = $this->utils->getMarkForComposition($student3, $program, $sequence2);
        $this->assertEquals($markForComposition, null);
    }*/
    
    public function testGetAppreciation()
    {
        $outPut = $this->utils->getAppreciation(3.5);
        $this->assertEquals('Null', $outPut);
        
        $outPut = $this->utils->getAppreciation(5);
        $this->assertEquals('Tres-Faible', $outPut);
        
        $outPut = $this->utils->getAppreciation(7.5);
        $this->assertEquals('Faible', $outPut);
        
        $outPut = $this->utils->getAppreciation(8);
        $this->assertEquals('Insuffisant', $outPut);
        
        $outPut = $this->utils->getAppreciation(11);
        $this->assertEquals('Passable', $outPut);
        
        $outPut = $this->utils->getAppreciation(18);
        $this->assertEquals('Tres-Bien', $outPut);
        
        $outPut = $this->utils->getAppreciation(20);
        $this->assertEquals('Excellent', $outPut);
    }
    
    public function testGetGlobalAppreciation()
    {
        //Test the result of the global appreciation
        //Case of 3.5   
        $outPut = $this->utils->getGlobalAppreciation(3.5);
        $this->assertEquals(false, $outPut['th_congratulation']);
        $this->assertEquals(false, $outPut['th_encouragement']);
        $this->assertEquals(false, $outPut['th']);
        $this->assertEquals(true, $outPut['exclusion']);
        //Case of 5
        $outPut = $this->utils->getGlobalAppreciation(5);
        $this->assertEquals(false, $outPut['th_congratulation']);
        $this->assertEquals(false, $outPut['th_encouragement']);
        $this->assertEquals(false, $outPut['th']);
        $this->assertEquals(false, $outPut['exclusion']);
        //Case of 12
        $outPut = $this->utils->getGlobalAppreciation(12);
        $this->assertEquals(false, $outPut['th_congratulation']);
        $this->assertEquals(false, $outPut['th_encouragement']);
        $this->assertEquals(true, $outPut['th']);
        $this->assertEquals(false, $outPut['exclusion']);
        //Case of 14
        $outPut = $this->utils->getGlobalAppreciation(14);
        $this->assertEquals(true, $outPut['th_congratulation']);
        $this->assertEquals(true, $outPut['th_encouragement']);
        $this->assertEquals(true, $outPut['th']);
        $this->assertEquals(false, $outPut['exclusion']);
    }
}

