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

class BuildMarkTableLTBHandlerTest extends WebTestCase
{
    private $em;
    private $application;
    private $buildMarkTableLTBHandler;

   

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        
        $this->application = new Application(static::$kernel);
        $this->em = $this->application->getKernel()->getContainer()->get('doctrine.orm.entity_manager');
        $this->buildMarkTableLTBHandler = $this->application->getKernel()->getContainer()->get('app.build_markTableLTB_handler');
    }
    
    /*This is the main intrance of the algorithm that suppose to generate 
     * markTable for LTB as design on physical Doc.
     * return a Matrix (array()) of markTables that suppose to be printed as pdf later
    */
    public function testGenerateMarkTableLTB()
    {
        //Prepare the required parameters (section & sequence)
        $section1 = $this->em->getRepository('AppBundle:Section')->find(2);
        $sequence1 = $this->em->getRepository('AppBundle:Sequence')->find(1);
        $this->assertEquals($section1->getName(), 'Sec 1ere G3B');
        $this->assertEquals($sequence1->getName(), '1er Trimestre');
        //Get all student for the given section based on fixture, they have to be two
        $students1 = $section1->getStudents();
        $this->assertEquals($students1[0]->getName(), 'Eleve_1_1ereG3B');
        $this->assertEquals($students1[1]->getName(), 'Eleve_2_1ereG3B');
        //Test it now
        $outPut = $this->buildMarkTableLTBHandler->generateMarkTableLTB($section1, $sequence1);
        //Make sure it return array of two items (parameters & markTables)
        $this->assertEquals(count($outPut), 2);
        $this->assertEquals($outPut['parameters'], array());
    }
    
    /*
     * This test is for the method that aims to generate markTable for one student
     */
    public function testBuildMarkTableOneStudentLTB()
    {
        /*****************TEST FOR 1ER TRIMESTRE********************/
        //Make sure fixture load the right student
        $sequence1 = $this->em->getRepository('AppBundle:Sequence')->find(1);
        $this->assertEquals($sequence1->getName(), '1er Trimestre');
        $student1 = $this->em->getRepository('AppBundle:Student')->find(2);
        $student2 = $this->em->getRepository('AppBundle:Student')->find(1);
        //Make sure student1 is Eleve_2_1ereG3B
        $this->assertEquals($student1->getName(), 'Eleve_2_1ereG3B');
        $this->assertEquals($student2->getName(), 'Eleve_1_1ereG3B');
        $section1 = $student1->getSection();
        //Make sure section is for Eleve_2_1ereG3B (Sec 1ere G3B)
        $this->assertEquals($section1->getName(), 'Sec 1ere G3B');
        //The selected programs have to be only for the right level: 1ere G3B
        $programs = $section1->getLevel()->getPrograms();
        $this->assertEquals($programs[0]->getName(), 'Prog Info 1ere G3');
        $this->assertEquals($programs[1]->getName(), 'Prog Maths 1ere G3');
        //For the current version of LTB2.yml only 4 items in $programs
        $this->assertEquals(count($programs), 4);
        
        //Now call the method and check MarkTable values for Eleve_2_1ereG3B in 1er Trimestre
        $markTableOneStudent1 = $this->buildMarkTableLTBHandler->buildMarkTableOneStudentLTB($student1, $sequence1);
        //Make sure the mark table is for Eleve_2_1ereG3B
        $this->assertEquals($markTableOneStudent1['student_name'], 'Eleve_2_1ereG3B');
        //Make sure $markTableOneStudent have 2 items (4 rows + student name) because there are two programs
        $this->assertEquals(count($markTableOneStudent1), 2);
        $this->assertEquals(count($markTableOneStudent1['rows']), 4);
        //Check the first row (program => 'Prog 1ere G3B', coef => 2, ...) for
        $this->assertEquals($markTableOneStudent1['rows'][0]['program_name'], 'Prog Info 1ere G3');
        $this->assertEquals($markTableOneStudent1['rows'][0]['coefficient'], 2);
        $this->assertEquals($markTableOneStudent1['rows'][0]['mark'], 12.75);
        $this->assertEquals($markTableOneStudent1['rows'][0]['mark_coefficient'], 25.5);
        $this->assertEquals($markTableOneStudent1['rows'][0]['teacher'], 'MAPOUKA Saint-Cyr');
        
        //Now call the method and check MarkTable values for Eleve_1_1ereG3B for th 1ere Trimestre
        $markTableOneStudent2 = $this->buildMarkTableLTBHandler->buildMarkTableOneStudentLTB($student2, $sequence1);
        //Make sure this marktable is for Eleve_1_1ereG3B
        $this->assertEquals($markTableOneStudent2['student_name'], 'Eleve_1_1ereG3B');
        //Make sure the mark table for Eleve_1_1ereG3B is right at this point
        //Make sure $markTableOneStudent have 3 items (two rows + student_name) because there are two programs
        $this->assertEquals(count($markTableOneStudent2), 2);
        //Check the first row (program => 'Prog 1ere G3B', coef => 2, ...) for
        $this->assertEquals($markTableOneStudent2['rows'][0]['program_name'], 'Prog Info 1ere G3');
        $this->assertEquals($markTableOneStudent2['rows'][0]['coefficient'], 2);
        $this->assertEquals($markTableOneStudent2['rows'][0]['mark'], 10.5);
        $this->assertEquals($markTableOneStudent2['rows'][0]['mark_coefficient'], 21);
        $this->assertEquals($markTableOneStudent2['rows'][0]['teacher'], 'MAPOUKA Saint-Cyr');
        //Check the second row (program => 'Prog 1ere G3B', coef => 2, ...) for
        $this->assertEquals($markTableOneStudent2['rows'][1]['program_name'], 'Prog Maths 1ere G3');
        $this->assertEquals($markTableOneStudent2['rows'][1]['coefficient'], 3);
        $this->assertEquals($markTableOneStudent2['rows'][1]['mark'], 6.5);
        $this->assertEquals($markTableOneStudent2['rows'][1]['mark_coefficient'], 19.5);
        $this->assertEquals($markTableOneStudent2['rows'][1]['teacher'], 'SARKO');
        $this->assertEquals($markTableOneStudent2['rows'][1]['appreciation'], 'Tres-Faible');
        
        //Test the case of a student that do not have any mark
        $student3 = $this->em->getRepository('AppBundle:Student')->find(5);
        //Make sure fixture data is right
        $this->assertEquals($student3->getName(), 'Eleve_3_1ereG3B');
        //Now call the method and check MarkTable values for Eleve_3_1ereG3B for th 1ere Trimestre
        $markTableOneStudent3 = $this->buildMarkTableLTBHandler->buildMarkTableOneStudentLTB($student3, $sequence1);
        //Make sure the mark table for Eleve_3_1ereG3B is right at this point
        //Make sure $markTableOneStudent have 3 items (4 rows + student_name) because there are two programs
        $this->assertEquals(count($markTableOneStudent3), 2);
        //Check the first row (program => 'Prog 1ere G3B', coef => 2, ...) for
        $this->assertEquals($markTableOneStudent3['rows'][0]['program_name'], 'Prog Info 1ere G3');
        $this->assertEquals($markTableOneStudent3['rows'][0]['coefficient'], 2);
        $this->assertEquals($markTableOneStudent3['rows'][0]['mark'], null);
        $this->assertEquals($markTableOneStudent3['rows'][0]['mark_coefficient'], null);
        $this->assertEquals($markTableOneStudent3['rows'][0]['teacher'], 'MAPOUKA Saint-Cyr');
        
        /*****************TEST FOR 2ND TRIMESTRE********************/
        //Make sure fixture load the right student
        $sequence2 = $this->em->getRepository('AppBundle:Sequence')->find(2);
        $this->assertEquals($sequence2->getName(), '2em Trimestre');
        $student1 = $this->em->getRepository('AppBundle:Student')->find(2);
        $student2 = $this->em->getRepository('AppBundle:Student')->find(1);
        //Make sure student1 is Eleve_2_1ereG3B
        $this->assertEquals($student1->getName(), 'Eleve_2_1ereG3B');
        $this->assertEquals($student2->getName(), 'Eleve_1_1ereG3B');
        $section1 = $student1->getSection();
        //Make sure section is for Eleve_2_1ereG3B (Sec 1ere G3B)
        $this->assertEquals($section1->getName(), 'Sec 1ere G3B');
        //The selected programs have to be only for the right level: 1ere G3B
        $programs = $section1->getLevel()->getPrograms();
        $this->assertEquals($programs[0]->getName(), 'Prog Info 1ere G3');
        $this->assertEquals($programs[1]->getName(), 'Prog Maths 1ere G3');
        //For the current version of LTB2.yml only 4 items in $programs
        $this->assertEquals(count($programs), 4);
        //Now call the method and check MarkTable values for Eleve_2_1ereG3B in 1er Trimestre
        $markTableOneStudent4 = $this->buildMarkTableLTBHandler->buildMarkTableOneStudentLTB($student1, $sequence2);
        //Make sure the mark table is for Eleve_2_1ereG3B
        $this->assertEquals($markTableOneStudent4['student_name'], 'Eleve_2_1ereG3B');
        //Make sure $markTableOneStudent have 2 items (4 rows + student name) because there are two programs
        $this->assertEquals(count($markTableOneStudent4), 2);
        $this->assertEquals(count($markTableOneStudent4['rows']), 4);
        //Check the first row (program => 'Prog 1ere G3B', coef => 2, ...) for
        $this->assertEquals($markTableOneStudent4['rows'][0]['program_name'], 'Prog Info 1ere G3');
        $this->assertEquals($markTableOneStudent4['rows'][0]['coefficient'], 2);
        $this->assertEquals($markTableOneStudent4['rows'][0]['mark'], 12.75);
        $this->assertEquals($markTableOneStudent4['rows'][0]['mark_coefficient'], 25.5);
        $this->assertEquals($markTableOneStudent4['rows'][0]['teacher'], 'MAPOUKA Saint-Cyr');
    }
}

