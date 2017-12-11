<?php

class Webkul_RmaSystem_Block_Adminhtml_Rma_New_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form([
            'id' => 'new_rma_form',
            'action'  => $this->getUrl('*/*/postNew'),
            'method' => 'post',
        ]);
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}