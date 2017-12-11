<?php


    class Webkul_RmaSystem_Block_Adminhtml_Label_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

        protected function _prepareForm() {
            $form = new Varien_Data_Form();
            $this->setForm($form);
            $fieldset = $form->addFieldset("label_form", array("legend" => $this->__("Label Information")));
            
            $fieldset->addField("title", "text", array(
                "label"     => $this->__("Title"),
                "name"      => "title",
    			"class"     => "required-entry",
                "required"  => true,
            ));

            $fieldset->addField("filename", "image", array(
                "label"     => $this->__("Label Image"),
                "name"      => "filename",
    			"class"     => "required-entry required-file",
                "required"  => true,
            ));

            $fieldset->addField("price", "text", array(
                "label"     => $this->__("Label Price"),
                "name"      => "price",
    			"class"     => "required-entry",
                "required"  => true,
            ));

            $fieldset->addField("status", "select", array(
                "label"     => $this->__("Status"),
                "class"     => "required-entry",
                "name"      => "status",
                "values"    => array(
                    array(
                        "value" => 1,
                        "label" => $this->__("Enabled"),
                    ),
                    array(
                        "value" => 2,
                        "label" => $this->__("Disabled"),
                    ),
                ),
            ));

            if (Mage::getSingleton("adminhtml/session")->getLabelData()) {
                $form->setValues(Mage::getSingleton("adminhtml/session")->getLabelData());
                Mage::getSingleton("adminhtml/session")->setLabelData(null);
            }
            elseif (Mage::registry("label_data"))
                $form->setValues(Mage::registry("label_data")->getData());
            return parent::_prepareForm();
        }

    }