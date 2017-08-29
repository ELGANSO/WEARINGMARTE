<?php

	class Webkul_RmaSystem_Block_Adminhtml_Reason_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

	    protected function _prepareForm() {
	        $form = new Varien_Data_Form();
	        $this->setForm($form);
	        $fieldset = $form->addFieldset("reason_form", array("legend" => $this->__("RMA Reason")));

			$fieldset->addField("reason", "textarea", array(
				"label"     =>  $this->__("Reason Provided"),
				"name"      =>  "reason",
				"class"     =>  "required-entry",
				"required"  =>  true
			));

			$fieldset->addField("status", "select", array(
				"label"     =>  $this->__("Status"),
				"name" 		=>  "status",
				"class"     =>  "required-entry",
				"required"  =>  true,
				"values"	=>  array(
									array("value" => "1","label" => $this->__("Enabled")),
									array("value" => "0","label" => $this->__("Disabled"))
								)
			));

	        if(Mage::registry("reason_data"))
	            $form->setValues(Mage::registry("reason_data")->getData());
	        return parent::_prepareForm();
	    }

	}