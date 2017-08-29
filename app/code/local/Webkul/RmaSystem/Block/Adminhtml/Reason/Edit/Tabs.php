<?php

    class Webkul_RmaSystem_Block_Adminhtml_Reason_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

        public function __construct() {
            parent::__construct();
            $this->setId("rmasystem_tabs");
            $this->setDestElementId("edit_form");
            $this->setTitle($this->__("Add/Edit Reason"));
        }

        protected function _beforeToHtml() {
            $this->addTab("form_section", array(
                "label"     => $this->__("RMA Reason"),
                "alt"       => $this->__("RMA Reason"),
                "content"   => $this->getLayout()->createBlock("rmasystem/adminhtml_reason_edit_tab_form")->toHtml()
            ));
            return parent::_beforeToHtml();
        }

    }