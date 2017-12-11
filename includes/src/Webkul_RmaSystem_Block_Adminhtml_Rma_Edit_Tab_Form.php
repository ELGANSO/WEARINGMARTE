<?php

	class Webkul_RmaSystem_Block_Adminhtml_Allrma_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

	    protected function _prepareForm() {
	        $form = new Varien_Data_Form();
	        $this->setForm($form);
	        $fieldset = $form->addFieldset("rma_form", array("legend" => $this->__("RMA Details")));
	        return parent::_prepareForm();
	    }

	}