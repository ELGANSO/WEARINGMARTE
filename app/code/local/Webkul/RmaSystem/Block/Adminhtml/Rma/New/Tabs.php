<?php

class Webkul_RmaSystem_Block_Adminhtml_Rma_New_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('rmasystem_tabs');
        $this->setDestElementId('new_rma_form');
        $this->setTitle($this->__('Devolución'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label' => $this->__('Detalles'),
            'alt' => $this->__('Detalles'),
            'content' => $this->getLayout()->createBlock('rmasystem/adminhtml_rma_new_tab_form')->toHtml()
        ));
        return parent::_beforeToHtml();
    }

}