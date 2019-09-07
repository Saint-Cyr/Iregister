<?php

namespace AppBundle\Service;

use AppBundle\Entity\Section;
use AppBundle\Entity\Sequence;
use AppBundle\Entity\Student;

class BuildMarkTableHandler
{
    //To store the entity manager
    private $em;
    private $utils;
    
    public function __construct($em, $utils) 
    {
        $this->em = $em;
        $this->utils = $utils;
    }
    
    public function generateMarkTable(Section $section, Sequence $sequence)
    {
        //Get all the parameters that are common to each marktable like school name, year, ...
        $parameters['section_name'] = $section->getName();
        $parameters['main_teacher'] = $section->getMainTeacher()->getTeacher()->getName();
        $parameters['student_number'] = $section->getStudentNumber();
        $parameters['total_coefficient'] = $section->getTotalCoefficient();
        
        //In order to set council date, we need it from setting
        $setting = $this->em->getRepository('AppBundle:Setting')->findOneBy(array('name' => 'setting'));
        $param['council_date'] = $setting->getCouncilDate();
        //Mark Tables
        $markTables = array();
        //Build the mark Table for each student (see algorithm in doc for more detailts)
        foreach ($section->getStudents() as $student){
            $markTables[] = $this->buildMarkTableOneStudent($student, $sequence);
        }
        
        return array('parameters' => $parameters, 'mark_tables' => $markTables);
    }
    
    public function buildMarkTableOneStudent(Student $student, Sequence $sequence)
    {
        $programs = $student->getSection()->getLevel()->getPrograms();
        //Prepare the variable that can content the markTable for one student
        $param['student_name'] = $student->getName();
        //Make sure the current student have a parent before we call the getParent() methode
        if($student->getStudentParent()){
            $markTableOneStudent = array('student_parent' => $student->getStudentParent()->getName());    
        }
        
        //Prepare the variable for totalMarkCoefficient
        $totalMarkCoefficient = null;
        //For each program belonging to the section of the current student,
        //Build column one after another
        foreach ($programs as $prog){
            //get the computed mark (for Devoir only) from a service
            //Notice that only mark for the right sequence (the activated one) must be used
            $computedMark = $this->utils->getComputedMark($student, $prog, false, $sequence);
            //Get mark for Composition
            $markForComposition = $this->utils->getMarkForComposition($student, $prog, $sequence);
            //Get the average: $composition + $computedMark / 3 according to the rule in CAR
            $average = ($computedMark + $markForComposition)/3;
            //Get appreciation
            $appreciation = $this->utils->getAppreciation($average);
            //Prepare the mark time coefficient
            $markCoef = ($average * $prog->getCoefficient()->getValue());
            //Prepare total mark Coefficient
            $totalMarkCoefficient = $totalMarkCoefficient + $markCoef;
            //Prepare teacher name Notice if null, then make sure to set it to unknown
            if(is_object($prog->getTeacher())){
                $teacherName = $prog->getTeacher()->getName();
            }else{
                $teacherName = 'Unknown';
            }
            
            //Build columns for the current program or current row (program Name, coef, mark/20, mark*Coef, ...)
            $row = array('program_name' => $prog->getName(),
                         'coefficient' => $prog->getCoefficient()->getValue(),
                         'mark' => $computedMark,
                         'mark_coefficient' => $markCoef,
                         'teacher' => $teacherName,
                         'appreciation' => $appreciation,
                         'mark_composition' => $markForComposition,
                         'average' => $average);
                         $rows[] = $row;
        }
        
        //Set the global appreciation
        $totalCoefficient = $student->getSection()->getTotalCoefficient();
        $globalAppreciation = $this->utils->getGlobalAppreciation($totalMarkCoefficient / $totalCoefficient);
        $param['total_coefficient'] = $totalCoefficient;
        $param['global_appreciation'] = $globalAppreciation;
        $param['total_mark_coefficient'] = $totalMarkCoefficient;
        //Those are the parameters that are related to a particular mark table (exple: Student Name, totalMarkCoef...)
        $markTableOneStudent['param'] = $param;
        
        $markTableOneStudent['rows'] = $rows;
        
        return $markTableOneStudent;
    }
}
