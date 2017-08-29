<?php

class Webkul_RmaSystem_Model_Ordercancel_UninvoicedOrder
{
    /** @var Mage_Sales_Model_Order */
    private $order;
    /** @var Webkul_RmaSystem_Model_Rma */
    private $rma;
    /** @var  Webkul_RmaSystem_Model_Items[] */
    private $rmaItems;

    /**
     * @param Mage_Sales_Model_Order $order
     * @param Webkul_RmaSystem_Model_Rma $rma
     * @return Webkul_RmaSystem_Model_Ordercancel_UninvoicedOrder
     */
    public function load(Mage_Sales_Model_Order $order, Webkul_RmaSystem_Model_Rma $rma)
    {
        $this->order = $order;
        $this->rma = $rma;

        // Cargamos los items del RMA
        $this->rmaItems = Mage::getModel('rmasystem/items')
            ->getCollection()
            ->addFieldToFilter('rma_id', $rma->getId());

        return $this;
    }

    public function cancel()
    {
        if(count($this->rmaItems) == $this->order->getTotalItemCount())
        {
            $this->log('Cancelando el pedido asociado %d...', $this->order->getId());
            $this->fullCancel();
        }
        else
        {
            $this->log('Cancelando parcialmente el pedido asociado %d...', $this->order->getId());
            $this->partialCancel();
        }
    }

    public function fullCancel()
    {
        // Hacemos una simple cancelación del pedido completo
        // Los stocks se restauran automáticamente
        $this->order->cancel();
        $this->order->save();
    }

    public function partialCancel()
    {
        $rmaItems = Mage::getModel('rmasystem/items')
            ->getCollection()
            ->addFieldToFilter('rma_id', $this->rma->getId());

        foreach($rmaItems as $rmaItem)
        {
            $orderItemId = $rmaItem['item_id'];
            $quantity = $rmaItem['qty'];

            // Recargamos el objeto de pedido para refrescar los items visibles
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->load($this->order->getId());

            // Obtenemos el item de pedido
            $item = $this->getOrderItem($order, $orderItemId);
            if($item === null)
            {
                // No debería pasar, peso si pasa continuamos con el siguiente
                // item de la devolución
                continue;
            }

            // Obtenemos los totales actuales del pedido
            $baseGrandTotal = $order->getBaseGrandTotal();
            $baseSubtotal = $order->getBaseSubtotal();
            $baseTax = $order->getBaseTaxAmount();
            $grandTotal = $order->getGrandTotal();
            $subtotal = $order->getSubtotal();
            $tax = $order->getTaxAmount();
            $baseSubtotalInclTax = $order->getBaseSubtotalInclTax();
            $subtotalInclTax = $order->getSubtotalInclTax();
            $totalItemCount = $order->getTotalItemCount();

            // Obtenemos el precio del ítem
            $itemPrice = $item->getRowTotal();
            $itemTax = $item->getTaxAmount();

            // Borramos el item (y su item simple asociado, si es un configurable)
            $simpleItem = Mage::helper('rmasystem')->getLinkedSimpleItem($item);
            if($simpleItem !== null)
            {
                $simpleItem->delete();
            }
            $item->delete();

            // Actualizamos los totales del pedido
            $order->setBaseGrandTotal($baseGrandTotal - $itemPrice - $itemTax);
            $order->setBaseSubtotal($baseSubtotal - $itemPrice);
            $order->setBaseTaxAmount($baseTax - $itemTax);
            $order->setGrandTotal($grandTotal - $itemPrice - $itemTax);
            $order->setSubtotal($subtotal - $itemPrice);
            $order->setTaxAmount($tax - $itemTax);
            $order->setBaseSubtotalInclTax($baseSubtotalInclTax - $itemPrice);
            $order->setSubtotalInclTax($subtotalInclTax - $itemPrice);
            $order->setTotalItemCount($totalItemCount - $quantity);
            $order->save();

            // Volvemos a añadir el stock del producto
            $item = ($simpleItem == null)
                ? $item
                : $simpleItem;

            Mage::helper('rmasystem')->restoreStock($item->getProductId(), $quantity);
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param int $itemId
     * @return Mage_Sales_Model_Order_Item|null
     */
    private function getOrderItem(Mage_Sales_Model_Order $order, $itemId)
    {
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        foreach($order->getAllVisibleItems() as $orderItem)
        {
            if($orderItem->getId() == $itemId)
            {
                return $orderItem;
            }
        }

        return null;
    }

    /**
     * @param string $message
     * @param array $args
     */
    private function log($message, $args = [])
    {
        return Mage::helper('rmasystem')->log($message, $args);
    }
}