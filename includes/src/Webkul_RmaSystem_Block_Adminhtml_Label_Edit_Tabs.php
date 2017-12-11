<?php

    class Webkul_RmaSystem_Block_Adminhtml_Label_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

        public function __construct() {
            parent::__construct();
            $this->setId("label_tabs");
            $this->setDestElementId("edit_form");
            $this->setTitle($this->__("Label Information"));
        }

        protected function _beforeToHtml() {
            $this->addTab("form_section", array(
                "label"     => $this->__("Label Information"),
                "alt"       => $this->__("Label information"),
                "content"   => $this->getLayout()->createBlock("rmasystem/adminhtml_label_edit_tab_form")->toHtml(),
            ));
            return parent::_beforeToHtml();
        }

    }