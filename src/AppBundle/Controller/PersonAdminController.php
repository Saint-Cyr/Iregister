<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

use Sonata\AdminBundle\Controller\CRUDController;

class PersonAdminController extends CRUDController
{
    public function batchActionGenerateCard(ProxyQueryInterface $selectedModelQuery, Request $request = null) {
        
        $selectedPersons = $selectedModelQuery->execute();
        
        //Build the barcode for each teacher
        foreach ($selectedPersons as $prs){
            
            $formatedId = str_pad($prs->getPosition(),3,"0",STR_PAD_LEFT); 
            
            $options = array(
                'code'   => $formatedId,
                'type'   => 'codabar',
                'format' => 'png',
                'width'  => 1,
                'height' => 10,
                //'color'  => array(0, 0, 0),
                //red color
                'color'  => array(186, 32, 32),
                );
             
            //$barcode = $this->get('cibincasso_barcode.generator')->generate($options);
            $prs->setBarcode($formatedId);
        }
        
        //return $this->render("@App/Default/pvc_verso_gate.html.twig", array('persons' => $persons));
        //return $this->render("@App/Default/pvc_recto_ikoue.html.twig", array('persons' => $persons));
        return $this->render("@App/Default/pvc_recto_gate.html.twig", array('persons' => $selectedPersons));
    }

}
