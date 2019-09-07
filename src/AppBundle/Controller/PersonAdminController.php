<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

use Sonata\AdminBundle\Controller\CRUDController;

class PersonAdminController extends CRUDController
{
    public function batchActionGenerateCard(ProxyQueryInterface $selectedPersons) {
        
        $prss = $selectedPersons->getMaxResults();
        foreach ($prss as $prs){
            var_dump('ok');exit;
            $formatedId = str_pad($prs->getId(),10,"0",STR_PAD_LEFT); 
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
            $barcode = $this->get('cibincasso_barcode.generator')->generate($options);
            $prs->setBarcode($barcode);
            $persons2[] = $prs;
        }
        
        return $this->render("@App/Default/persons_card.html.twig", array('persons2' => $persons2));
    }
}
