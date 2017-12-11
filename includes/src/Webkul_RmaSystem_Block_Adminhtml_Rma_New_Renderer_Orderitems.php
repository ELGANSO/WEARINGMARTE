<?php

class Webkul_RmaSystem_Block_Adminhtml_Rma_New_Renderer_Orderitems extends Varien_Data_Form_Element_Abstract
{
    public function getElementHtml()
    {
        $orderId = Mage::registry('rma_order_id');

        return Mage::app()
            ->getLayout()
            ->createBlock('rmasystem/adminhtml_rma_new_renderer_orderitems_block')
            ->setOrderId($orderId)
            ->setTemplate('rmasystem/orderitems.phtml')
            ->toHtml();
    }
}