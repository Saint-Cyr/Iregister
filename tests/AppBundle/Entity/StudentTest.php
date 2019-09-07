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

class StudentTest extends WebTestCase
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
     * this is valid for fixture LTB2.yml
     * @deprecated since 0.5.1
     */
    public function testGetMarksBySequenceByProgram()
    {
        //Test the result for 1er Trimestre
        //Get the sequence 1er Trimester
        $sequence1 = $this->em->getRepository('AppBundle:Sequence')->find(1);
        //Get the student Eleve_2_1ereG3B
        $student1 = $this->em->getRepository('AppBundle:Student')->find(2);
        //Get the program
        $program1 = $this->em->getRepository('AppBundle:Program')->find(1);
        //Make sure the loaded data from the fixtures are right
        $this->assertEquals($sequence1->getName(), '1er Trimestre');
        $this->assertEquals($student1->getName(), 'Eleve_2_1ereG3B');
        $this->assertEquals($program1->getName(), 'Prog Info 1ere G3');
        //Test the method now
        $marks1 = $student1->getMarksBySequenceByProgram($sequence1, $program1);
        //Eleve_2_1ereG3B only have 3 marks for the 1er Trimestre in "Prog Info G3"
        $this->assertEquals(count($marks1), 2);
        //Make sure the contain of the marks are right Notice that Eleve_2_1ereG3B have 14.5 and 11 (Prog Info 1ere G3B) 
        //for the 1er Trimester (see doc fixtures)
        $this->assertEquals($marks1[0]->getValue(), 14.5);
        $this->assertEquals($marks1[1]->getValue(), 11);
        
        //Test the result for 2nd Trimestre
        //Notice that for this case we use another program (Maths 1er G3)
        //Get the sequence 1er Trimester
        $sequence2 = $this->em->getRepository('AppBundle:Sequence')->find(2);
        //Get the program (different one): Maths 1ere G3 instead of Prog Info G3 see Doc/LTB2.doc
        $program2 = $this->em->getRepository('AppBundle:Program')->find(5);
        //Make sure the loaded data from the fixtures are right
        $this->assertEquals($sequence2->getName(), '2em Trimestre');
        $this->assertEquals($program2->getName(), 'Prog Maths 1ere G3');
        //Test the method now
        $marks2 = $student1->getMarksBySequenceByProgram($sequence2, $program2);
        //Eleve_2_1ereG3B only have 1 marks for the 2em Trimestre in "Prog Maths G3"
        $this->assertEquals(count($marks2), 1);
        //Make sure the contain of the marks are right Notice that Eleve_2_1ereG3B have 7.5 in Maths the 2nd Trimester (see doc fixtures)
        $this->assertEquals($marks2[0]->getValue(), 7.5);
        
        
        //Test the result for 3em Trimestre
        //Notice that for this case we use another program $program1 (Prog Info 1er G3)
        //Get the sequence 3em Trimester
        $sequence3 = $this->em->getRepository('AppBundle:Sequence')->find(3);
        //Make sure the loaded data from the fixtures are right
        $this->assertEquals($sequence3->getName(), '3em Trimestre');
        $this->assertEquals($program1->getName(), 'Prog Info 1ere G3');
        //Test the method now
        $marks3 = $student1->getMarksBySequenceByProgram($sequence3, $program1);
        //Eleve_2_1ereG3B only have 2 marks for the 3em Trimestre in "Prog Info G3"
        $this->assertEquals(count($marks3), 2);
        //Make sure the contain of the marks are right (see prevew test)
        $this->assertEquals($marks3[0]->getValue(), 14.5);
    }
}

