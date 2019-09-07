<?php

/*
 * This file is part of Components of Academ project
 * By contributor S@int-Cyr MAPOUKA
 * (c) iTech <mapoukacyr@yahoo.fr>
 * For the full copyrght and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Tests\AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Teacher;

class UtilsLTBTest extends WebTestCase
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
        $this->utils = $this->application->getKernel()->getContainer()->get('app.utilsLTB');
    }
    
    /*
     * this is valid for fixture LTB2.yml
     */
    public function testComputedMark()
    {
        /***********Test the avarage of Eleve_2_1ereG3B for Prog Info 1ereG3B in the 1er Trimestre************/
        $student1 = $this->em->getRepository('AppBundle:Student')->find(2);
        //Make sure student is Eleve_2_1ereG3B
        $this->assertEquals($student1->getName(), 'Eleve_2_1ereG3B');
        //Make sure program is Info 1ere G3
        $program1 = $this->em->getRepository('AppBundle:Program')->find(1);
        //Make sure fixtures is right
        $sequence1 = $this->em->getRepository('AppBundle:Sequence')->find(1);
        $this->assertEquals($program1->getName(), 'Prog Info 1ere G3');
        $this->assertEquals($sequence1->getName(), '1er Trimestre');
        //Get all the marks for the 1st sequence (1er Trimester) see StudentTest to know more...
        $marks1 = $student1->getMarks();
        //Make sure mark values are right as in fixture and it is only two for ELeve_2_G3B
        $this->assertEquals(count($marks1), 9);
        $this->assertEquals($marks1[0]->getValue(), 14.5);
        $this->assertEquals($marks1[1]->getValue(), 11);
        //test the avarage computing methode now
        $avarage1 = $this->utils->getComputedMark($student1, $program1, $sequence1);
        $this->assertEquals($avarage1, 12.75);
        
        /**********Test the average of Eleve_2_1ereG3B for Prog Info 1ereG3 in 2nd Trimestre**********/
        $sequence2 = $this->em->getRepository('AppBundle:Sequence')->find(2);
        $this->assertEquals($sequence2->getName(), '2em Trimestre');
        $avarage2 = $this->utils->getComputedMark($student1, $program1, $sequence2);
        $this->assertEquals($avarage2, 14);
        
        //Trying to make calculate the average of a program that is not belong to an avaluation
        //Make sure program is Francais 1ere G3
        $program2 = $this->em->getRepository('AppBundle:Program')->find(7);
        $this->assertEquals($program2->getName(), 'Prog. Francais 1erG3');
        $avarage2 = $this->utils->getComputedMark($student1, $program2, $sequence1);
        $this->assertEquals($avarage2, null);
    }
}

