<?php

namespace AppBundle\Service;

use AppBundle\Entity\Student;
use AppBundle\Entity\Program;
use AppBundle\Entity\Sequence;

class UtilsLTB
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
     * the current prog ($prog) and the given sequence then give back the avarage value
     * Exple: if the student have two mark in Prog Info 1ere G3 such as 14.5 & 11
     * then the avarage will be (14.5+11)/2
     */
    public function getComputedMark(Student $student, Program $prog, Sequence $sequence)
    {   
        //Get the mark number
        $markNb = 0;
        //Prepare the variable that will hold all the selected mark.(the one belongs to $prog)
        $selectedMark = 0;
        //Select only marks that belong to $prog
        foreach ($student->getMarks() as $mark){
            
                
                //if this mark belongs to $prog and is for the current sequence then select it
                if(($mark->getEvaluation()->getProgram()->getId() == $prog->getId())
                    && ($mark->getEvaluation()->getEvaluationType()->getName() == 'Devoire')
                    &&($mark->getEvaluation()->getSequence()->getId() == $sequence->getId())
                   ){
                    //Make sure to count the number of evaluation for the current program
                    $markNb = $markNb + 1;
                    $selectedMark = $selectedMark + $mark->getValue();
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
}
