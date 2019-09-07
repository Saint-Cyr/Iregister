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

class BuildMarkTableHandlerTest extends WebTestCase
{
    private $em;
    private $application;
    private $buildMarkTableHandler;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        
        $this->application = new Application(static::$kernel);
        $this->em = $this->application->getKernel()->getContainer()->get('doctrine.orm.entity_manager');
        $this->buildMarkTableHandler = $this->application->getKernel()->getContainer()->get('app.build_marktable_handler');
    }
    
    /*This is the main intrance of the algorithm that suppose to generate 
     * markTable for standard as design on physical Doc.
     * return a Matrix (array()) of markTables that suppose to be printed as pdf later
    */
    public function testGenerateMarkTable()
    {
        //Prepare the required parameters (section & sequence)
        $section = $this->em->getRepository('AppBundle:Section')->find(1);
        $sequence = $this->em->getRepository('AppBundle:Sequence')->find(1);
        $this->assertEquals($section->getName(), 'Sec 3eme 1');
        $this->assertEquals($sequence->getName(), '1er Trimestre');
        //Get all student for the given section based on fixture, they have to be two
        $students = $section->getStudents();
        $this->assertEquals($students[0]->getName(), 'KOSSI');
        $this->assertEquals($students[1]->getName(), 'YASSI');
        //Test it now
        $outPut = $this->buildMarkTableHandler->generateMarkTable($section, $sequence);
        //Make sure the parameters that are common to each mark table is properly loaded.
        $this->assertEquals(count($outPut), 2);
        $this->assertEquals($outPut['parameters']['section_name'], 'Sec 3eme 1');
        $this->assertEquals($outPut['parameters']['student_number'], 2);
        $this->assertEquals($outPut['parameters']['main_teacher'], 'BANGOU Sylvain');
        //Make sure the total contained in common parameters for each mark table is right
        $this->assertEquals($outPut['parameters']['total_coefficient'], 6);
    }
    
    public function testBuildMarkTableOneStudent()
    {
        //Make sure fixture load the right student
        $sequence = $this->em->getRepository('AppBundle:Sequence')->find(1);
        $this->assertEquals($sequence->getName(), '1er Trimestre');
        $student2 = $this->em->getRepository('AppBundle:Student')->find(2);
        $student1 = $this->em->getRepository('AppBundle:Student')->find(1);
        //Make sure student is KOYASSANGOU
        $this->assertEquals($student1->getName(), 'KOSSI');
        $section = $student1->getSection();
        //Make sure section is for KOSSI (Sec 3eme 1)
        $this->assertEquals($section->getName(), 'Sec 3eme 1');
        //Check fixture 
        $this->assertEquals(count($student1->getMarks()), 5);
        $this->assertEquals($student1->getMarks()[0]->getValue(), 12.5);
        $this->assertEquals($student1->getMarks()[1]->getValue(), 8.5);
        $this->assertEquals($student1->getMarks()[2]->getValue(), 9.5);
        $this->assertEquals($student1->getMarks()[3]->getValue(), 7.5);
        $this->assertEquals($student1->getMarks()[4]->getValue(), 9.5);
        
        $this->assertEquals($student1->getMarks()[0]->getEvaluation()->getEvaluationType()->getName(), 'Devoire');
        $this->assertEquals($student1->getMarks()[1]->getEvaluation()->getEvaluationType()->getName(), 'Devoire');
        $this->assertEquals($student1->getMarks()[2]->getEvaluation()->getEvaluationType()->getName(), 'Composition');
        //The selected programs have to be only for the right level: 3eme
        $programs = $section->getLevel()->getPrograms();
        //Make sure there are only 
        $this->assertEquals(count($programs), 2);
        $this->assertEquals($programs[0]->getName(), 'Prog Physique Chimie 3eme');
        $this->assertEquals($programs[1]->getName(), 'Prog Maths 3eme');
        //For the current version of STD1.yml only 2 items in $programs
        $this->assertEquals(count($programs), 2);
        $this->assertEquals('KOSSI', $student1->getName());
        
        //Now call the method and check MarkTable values for KOSSI
        $markTableOneStudent = $this->buildMarkTableHandler->buildMarkTableOneStudent($student1, $sequence);
        $this->assertEquals($markTableOneStudent['param']['student_name'], 'KOSSI');
        //Make sure $markTableOneStudent have ...
        $this->assertEquals(count($markTableOneStudent), 2);
        $this->assertEquals(count($markTableOneStudent['rows']), 2);
        //Check the first row (program => 'Prog 1ere G3B', coef => 2, ...) for
        $this->assertEquals($markTableOneStudent['rows'][0]['program_name'], 'Prog Physique Chimie 3eme');
        $this->assertEquals($markTableOneStudent['rows'][0]['coefficient'], 3);
        $this->assertEquals($markTableOneStudent['rows'][0]['mark'], 10.5);
        $this->assertEquals($markTableOneStudent['rows'][0]['mark_composition'], 19);
        $this->assertEquals($markTableOneStudent['rows'][0]['average'], 9.8333333333333339);
        $this->assertEquals($markTableOneStudent['rows'][0]['mark_coefficient'], 29.5);
        $this->assertEquals($markTableOneStudent['rows'][0]['teacher'], 'BANGOU Sylvain');
        $this->assertEquals($markTableOneStudent['rows'][0]['appreciation'], 'Insuffisant');
        $this->assertEquals($markTableOneStudent['param']['total_mark_coefficient'], 56);
        //Check the seconde row
        //Check the first row (program => 'Prog 1ere G3B', coef => 2, ...) for
        $this->assertEquals($markTableOneStudent['rows'][1]['program_name'], 'Prog Maths 3eme');
        $this->assertEquals($markTableOneStudent['rows'][1]['coefficient'], 3);
        $this->assertEquals($markTableOneStudent['rows'][1]['mark'], 7.5);
        $this->assertEquals($markTableOneStudent['rows'][1]['mark_composition'], 19);
        $this->assertEquals($markTableOneStudent['rows'][1]['average'], 8.8333333333333339);
        $this->assertEquals($markTableOneStudent['rows'][1]['mark_coefficient'], 26.5);
        $this->assertEquals($markTableOneStudent['rows'][1]['teacher'], 'Unknown');
        $this->assertEquals($markTableOneStudent['rows'][1]['appreciation'], 'Insuffisant');
        $this->assertEquals($markTableOneStudent['param']['total_mark_coefficient'], 56);
        //Check the global appreciation
        $this->assertEquals($markTableOneStudent['param']['total_coefficient'], 6);
        $this->assertEquals($markTableOneStudent['param']['global_appreciation'], array('th_congratulation' => false,
                                                                                        'th_encouragement' => false,
                                                                                        'th' => false,
                                                                                        'exclusion' => false,));
    }
}

