<?php

class Webkul_RmaSystem_Model_Observer
{
    public function addRmaButton()
    {
        // Obtenemos el pedido mostrado
        /** @var Mage_Adminhtml_Block_Sales_Order_View $block */
        $block = Mage::app()->getLayout()->getBlock('sales_order_edit');
        $order = $block->getOrder();

        // Si el pedido está en estado cancelado o cerrado, salimos directamente
        if($order->isCanceled() || $order->getState() == Mage_Sales_Model_Order::STATE_CLOSED)
        {
            return;
        }

        // Añadimos un botón que nos lleve a la página de generar devolución
        $url = Mage::helper('adminhtml')->getUrl('rmasystem/adminhtml_rma/new', [ 'order_id' => $order->getId() ]);
        $block->addButton('do_something_crazy', array(
            'label' => 'Devolver',
            'onclick' => "setLocation('{$url}')",
            'class' => 'go'
        ));
    }
}