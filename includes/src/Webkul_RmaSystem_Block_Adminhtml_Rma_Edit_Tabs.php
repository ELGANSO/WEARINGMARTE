<?php

    class Webkul_RmaSystem_Block_Adminhtml_Rma_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

        public function __construct() {
            parent::__construct();
            $this->setId("rmasystem_tabs");
            $this->setDestElementId("edit_form");
            $this->setTitle($this->__("RMA Information"));
        }

        protected function _beforeToHtml() {
            $this->addTab("form_section", array(
                "label"     =>  $this->__("RMA Details"),
                "alt"       =>  $this->__("RMA Details"),
                "content"   =>  $this->getLayout()->createBlock("page/html")->setTemplate("rmasystem/form.phtml")->toHtml()
            ));
            return parent::_beforeToHtml();
        }

    }