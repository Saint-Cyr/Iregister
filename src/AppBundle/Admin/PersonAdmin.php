<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class PersonAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('age')
            ->add('area')
            ->add('status')
            ->add('sexe')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('collectedImage')
            ->add('image', null, array('template' => 'AppBundle:Default:list.html.twig'))
            ->add('name', null, array('editable' => true))
            ->add('firstName')
            ->add('age')
            ->add('position', null, array('editable' => true))
            ->add('area')
            ->add('status')
            ->add('collectedImageUpdated', null, array('editable' => true))
            ->add('sexe')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
    
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        //The super-user have to be able to edit barcode
        $disabled = true;
        
        if($this->isGranted('SUPER-ADMIN')){
            $disabled = false;       
        }
        
        //$option = (preg_match('/_edit$/', $this->getRequest()->get('_route'))) ? false : true;
        $formMapper
        ->with('General information', array('class' => 'col-md-8'))
            ->add('name')
            ->add('area')
            ->add('status')
            ->add('sexe')
        ->end();
        $formMapper
        ->with('Image', array('class' => 'col-md-4'))
            ->add('collectedImage')
            ->add('file', 'file', array('required' => false))
        ->end()
        ;
    }
    
    public function getBatchActions()
    {
        // retrieve the default batch actions (currently only delete)
        $actions = parent::getBatchActions();
        
        $actions['generate_card'] = array('label' => 'Gen. #ID Card',
                                          'translate_domain' => 'SonataAdminBundle',
                                          'ask_confirmation' => false);

        return $actions;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        ;
    }
    
     public function prePersist($image)
    {
        $this->manageFileUpload($image);
    }

    public function preUpdate($image)
    {
        $this->manageFileUpload($image);
    }

    private function manageFileUpload($image)
    {
        if ($image->getFile()) {
            $image->refreshUpdated();
        }
    }
}
