<?php

class CSVExport_IndexController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
    	$form = new Zend_Form;
		$form->setAction(url(array('module'=>'csv-export', 'controller'=>'export', 'action'=>'csv'), 'default'))
			 ->setMethod('post');
		
		$element = new Zend_Form_Element_File('csv');

		$form->setAttrib('enctype', 'multipart/form-data');
		$form->addElement($element, 'csv');

		$this->view->assign('form', $form);
    }
}