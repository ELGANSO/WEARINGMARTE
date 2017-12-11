<?php

    class Webkul_RmaSystem_Block_Adminhtml_Label_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

        public function __construct() {
            parent::__construct();
            $this->_objectId = "id";
            $this->_blockGroup = "rmasystem";
            $this->_controller = "adminhtml_label";
            $this->_updateButton("save", "label", $this->__("Save Item"));
            $this->_updateButton("delete", "label", $this->__("Delete Item"));
            $this->_addButton("saveandcontinue", array(
                "label"     => $this->__("Save And Continue Edit"),
                "onclick"   => "saveAndContinueEdit()",
                "class"     => "save",
            ), -100);
            $this->_formScripts[] = "
                function saveAndContinueEdit(){
                    editForm.submit($('edit_form').action+'back/edit/');
                }";
        }

        public function getHeaderText() {
            if (Mage::registry("label_data") && Mage::registry("label_data")->getId())
                return $this->__("Edit Label ").$this->htmlEscape(Mage::registry("label_data")->getTitle());
            else
                return $this->__("Add Label");
        }

    }