<?php

    class Webkul_RmaSystem_Block_Adminhtml_Reason_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

        public function __construct() {
            parent::__construct();
            $this->_objectId    = "id";
            $this->_blockGroup  = "rmasystem";
            $this->_controller  = "adminhtml_reason";
            $this->_addButton("saveandcontinue", array(
                "label"     => $this->__("Save And Continue Edit"),
                "onclick"   => "saveAndContinueEdit()",
                "class"     => "save",
                    ), -100);
            $this->_formScripts[] = "function saveAndContinueEdit(){
                                        editForm.submit($('edit_form').action+'back/edit/');
                                    }";
        }

        public function getHeaderText() {
            if(Mage::registry("reason_data")->getId() > 0)
                return $this->__("View Reason id").$this->htmlEscape(Mage::registry("reason_data")->getId());
            else
                return $this->__("Create New Reason");
        }

    }