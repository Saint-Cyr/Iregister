<?php

namespace AppBundle\Service;

use AppBundle\Entity\Student;
use AppBundle\Entity\Program;
use AppBundle\Entity\Sequence;

class UtilsSTD
{
    //To store the entity manager
    private $em;
    private $securityHandler;

    public function __construct($em, $securityHandler) 
    {
        $this->securityHandler = $securityHandler;
        $this->em = $em;
    }
    
    public function getSecurityHandler()
    {
        return $this->securityHandler;
    }
    
    /*
     * This methode suppose to compute all mark for a given student based on
     * the current prog ($prog) and give back the avarage value
     * Exple: if the student have two mark in Math 1ere C such as 12.5 & 7.5
     * then the avarage will be (12.5+7.5)/2
     * By the 3rd argument (default = true) indicate whether considere only Devoir (false) or
     * both Devoir and Composition (true)
     */
    public function getComputedMark(Student $student, Program $prog, $allMark = true)
    {
        
        //Get the mark number
        $markNb = 0;
        //Prepare the variable that will hold all the selected mark.(the one belongs to $prog)
        $selectedMark = 0;
        //Select only marks that belong to $prog
        foreach ($student->getMarks() as $mark){
            //if $allMark = false, then treated only mark of Devoir
            if(!$allMark){
                
                //if this mark belongs to $prog and is for Devoir, then select it
                if(($mark->getEvaluation()->getProgram()->getId() == $prog->getId())
                        && ($mark->getEvaluation()->getEvaluationType()->getName() == 'Devoire')){
                    //Make sure to count the number of evaluation for the current program
                    $markNb = $markNb + 1;
                    $selectedMark = $selectedMark + $mark->getValue();
                }
            //else, it means we are in case of LTB.
            }else{
                
                //if this mark belongs to $prog then select it
                if($mark->getEvaluation()->getProgram()->getId() == $prog->getId()){
                    //Make sure to count the number of evaluation for the current program
                    $markNb = $markNb + 1;
                    $selectedMark = $selectedMark + $mark->getValue();
                }
                
            }
        }
        //return the average value of the marks for the program $prog only
        //if $markNb != 0
        if($markNb > 0){
            return ($selectedMark / $markNb);
        }else{
            return null;
        }
        
    }
    
    /*
     * return a mark (value * 2) when it related evaluation type is composition
     * this is for STD mode
     */
    public function getMarkForComposition(Student $student, Program $program, Sequence $sequence)
    {
        $markComposition = null;
        //Get all marks
        $marks = $student->getMarks();
        foreach ($marks as $mark){
            //Make sure mark is for the current $sequence and it evaluation type is Composition
            if(($mark->getEvaluation()->getEvaluationType() == 'Composition')
                    &&
                ($mark->getEvaluation()->getSequence()->getId() == $sequence->getId())
                    && 
                    ($mark->getEvaluation()->getProgram()->getId() == $program->getId()))
            {
                $markComposition = $mark->getValue();
            }
        }
        
        if($markComposition){
            //Don't forget that by default a composition mark is times 2
            return ($markComposition * 2);
        }else{
            return null;
        }
    }
    
    public function getAppreciation($mark)
    {
        if($mark >= 0 && $mark < 5)
            {
            //Null
            return 'Null';
        }
        elseif($mark >= 5 && $mark < 7)
            {
            //Tres Faible
            return 'Tres-Faible';
        }
        elseif($mark >= 7 && $mark < 8)
            {
            //Faible
            return 'Faible';
        }
        elseif($mark >= 8 && $mark < 10)
            {
            //Insuffisant
            return 'Insuffisant';
        }
        elseif($mark >= 10 && $mark < 11)
            {
            //Moyenne
            return 'Moyenne';
        }
        elseif($mark >= 11 && $mark < 12)
            {
            //Passable
            return 'Passable';
        }
        elseif($mark >= 12 && $mark < 14)
            {
            //Assez-Bien
            return 'Assez-Bien';
        }
        elseif($mark >= 14 && $mark < 15)
            {
            //Bien
            return 'Bien';
        }
        elseif($mark >= 15 && $mark < 19)
            {
            //Tres Bien
            return 'Tres-Bien';
        }
        elseif($mark >= 19 && $mark < 21){
            //Excellent
            return 'Excellent';
        }
        
        return 'Unknown';
    }
    
    /**
     * @return array() like $tab['th_congratulation'], $tab['th_ this methode return the global
     * appreciation about the globale mark for a sequence.
     * 
     * 
     */
    public function getGlobalAppreciation($mark)
    {
        if($mark >= 0 && $mark < 5)
            {
            //Null
            return array('th_congratulation' => false,
                         'th_encouragement' => false,
                         'th' => false,
                         'exclusion' => true,);
        }
        elseif($mark >= 5 && $mark < 7)
            {
            //Tres Faible
            return array('th_congratulation' => false,
                         'th_encouragement' => false,
                         'th' => false,
                         'exclusion' => false,);
        }
        elseif($mark >= 7 && $mark < 8)
            {
            //Faible
            return array('th_congratulation' => false,
                         'th_encouragement' => false,
                         'th' => false,
                         'exclusion' => false,);
        }
        elseif($mark >= 8 && $mark < 10)
            {
            //Insuffisant
            return array('th_congratulation' => false,
                         'th_encouragement' => false,
                         'th' => false,
                         'exclusion' => false,);
        }
        elseif($mark >= 10 && $mark < 11)
            {
            //Moyenne
            return array('th_congratulation' => false,
                         'th_encouragement' => false,
                         'th' => false,
                         'exclusion' => false,);
        }
        elseif($mark >= 11 && $mark < 12)
            {
            //Passable
            return array('th_congratulation' => false,
                         'th_encouragement' => false,
                         'th' => false,
                         'exclusion' => false,);
        }
        elseif($mark >= 12 && $mark < 14)
            {
            //Assez-Bien
            return array('th_congratulation' => false,
                         'th_encouragement' => false,
                         'th' => true,
                         'exclusion' => false,);
        }
        elseif($mark >= 14 && $mark < 15)
            {
            //Bien
            return array('th_congratulation' => true,
                         'th_encouragement' => true,
                         'th' => true,
                         'exclusion' => false,);
        }
        elseif($mark >= 15 && $mark < 19)
            {
            //Tres Bien
            return array('th_congratulation' => true,
                         'th_encouragement' => true,
                         'th' => true,
                         'exclusion' => false,);
        }
        elseif($mark >= 19 && $mark < 21){
            //Excellent
            return array('th_congratulation' => true,
                         'th_encouragement' => true,
                         'th' => true,
                         'exclusion' => false,);
        }
        
        return array();
        
    }
}
