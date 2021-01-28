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
            
            $formatedId = str_pad($prs->getPosition(),4,"0",STR_PAD_LEFT); 
            
            $options = array(
                'code'   => $formatedId,
                'type'   => 'ean13',
                'format' => 'png',
                'width'  => 1,
                'height' => 35,
                'color'  => array(0, 0, 0),
                //red color
                'color'  => array(11, 164, 239),
                );
            $barcode = $this->get('cibincasso_barcode.generator')->generate($options);
            //$prs->setBarcode($barcode);
            $prs->setBarcode($formatedId);
        }
        
        //return $this->render("@App/Default/pvc_verso.html.twig", array('persons' => $selectedPersons));
        //return $this->render("@App/Default/pvc_recto_ikoue.html.twig", array('persons' => $persons));
        //return $this->render("@App/Default/pvc_recto_ikoue.html.twig", array('persons' => $persons));
        //return $this->render("@App/Default/pvc_recto_gate.html.twig", array('persons' => $selectedPersons));
        //return $this->render("@App/Default/pvc_recto.html.twig", array('persons' => $selectedPersons, 'id' => $formatedId));
        return $this->render("@App/Default/pvc_recto_unicef.html.twig", array('persons' => $selectedPersons));
    }

}
