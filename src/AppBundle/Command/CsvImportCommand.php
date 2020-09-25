<?php
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use AppBundle\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;

class CsvImportCommand extends Command
{
    private $em;
    
    public function __construct(EntityManagerInterface $em) 
    {
        parent::__construct();
        $this->em = $em;
        
    }
    
    protected function configure()
    {
        $this
            ->setName('csv:import')
            ->setDescription('Import a mock csv file by @Saint-Cyr MAPOUKA')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->writeln('File Processing...');
        $io->title('==== <options=bold;fg=red>iReg®</></> is a trademark of Saint-Cyr MAPOUKA. All rights reserved. || By iSTech ( © 2016-2020) ==============');
        //Instatiate the csv reader library
        $reader = \League\Csv\Reader::createFromPath('%kernel.root_dir%/../web/data/clpr_members_list.csv');
        $results = $reader->fetchAssoc();
        
        $io->progressStart();
        
        //set a variable to count the number of processed
        $recorded = 0;
        $unrecorded = 0;
        foreach ($results as $row){
            $_person = $this->em->getRepository('AppBundle:Person')->findOneBy(array('name' => $row['name']));
            //if person does not yet exist in DB
            if(!$_person){
                //create person object
                $person = new Person();
                $person->setName($row['name']);
                //$person->setFirstName($row['first_name']);
                $person->setArea($row['area']);
                //$person->setStatus($row['status']);
                $person->setSexe($row['sexe']);
                //$person->setAge($row['age']);
                $person->setCommune($row['juridiction']);
                $person->setPosition($row['position']);
                $person->setCollectedImage($row['image']);
                $this->em->persist($person);
                $io->progressAdvance();
                $recorded++;   
            }else{
                $unrecorded++;
            }
        }
        
        $io->progressFinish();
        $this->em->flush();
        
        if($unrecorded){
            $io->note($unrecorded.' Person(s) has not been recorded.');
        }
        if($recorded){
            $io->success('Recorded: '.$recorded.' Unrecorded: '.$unrecorded);
        }
        
    }
}