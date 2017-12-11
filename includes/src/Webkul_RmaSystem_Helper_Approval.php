<?php

require_once Mage::getModuleDir('', 'Webkul_RmaSystem') . '/lib/apiRedsys.php';

class Webkul_RmaSystem_Helper_Approval extends Mage_Core_Helper_Abstract
{
    /** @var int */
    private $rmaId;

    public function approveRma(Webkul_RmaSystem_Model_Rma $rma)
    {
        Mage::register('rmasystem_approved_rma_id', $rma->getId());

        $this->rmaId = $rma->getId();
        $this->log('RMA aprobado.', []);

        // Marcamos el RMA como aprobado
        $rma->setStatus(Webkul_RmaSystem_Model_Constants::StatusAccepted);
        $rma->save();

        switch($rma['resolution_type'])
        {
            case Webkul_RmaSystem_Model_Constants::ResolutionTypeRefund:
                $this->log('Iniciando la devolución del pedido...');
                $this->cancelOrder($rma);
                break;

            case Webkul_RmaSystem_Model_Constants::ResolutionTypeExchange:
                $this->log('Iniciando el cambio de tallas...');
                $this->exchangeItemSize($rma);
                break;

            default:
                Mage::getSingleton('adminhtml/session')
                    ->addError('Tipo de devolución no válida: ' . $rma['resolution_type']);
        }
    }

    private function exchangeItemSize(Webkul_RmaSystem_Model_Rma $rma)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')
            ->load($rma['order_id']);

        $orderItems = $order->getAllItems();

        $items = Mage::getModel('rmasystem/items')
            ->getCollection()
            ->addFieldToFilter('rma_id',$rma->getId());

        $sizeAttribute = Mage::getModel('catalog/product')->getResource()->getAttribute('size');

        /** @var Webkul_RmaSystem_Model_Items $rmaItem */
        foreach($items as $rmaItem)
        {
            // Cargamos el item de pedido configurable
            /** @var Mage_Sales_Model_Order_Item $parentItem */
            $parentItem = Mage::getModel('sales/order_item')
                ->load($rmaItem['item_id']);

            // Y su item simple asociado
            $childItem = static::getAssociatedSimpleItem($parentItem, $orderItems);

            // Cargamos el producto original
            $previousProduct = Mage::getModel('catalog/product')
                ->load($childItem->getProductId());

            // Y el nuevo producto solicitado
            $replacementeProductId = $rmaItem['requested_product_id'];
            /** @var Mage_Catalog_Model_Product $replacementProduct */
            $replacementProduct = Mage::getModel('catalog/product')->load($replacementeProductId);

            // Actualizamos el child item
            $childItem->setProductId($replacementeProductId);
            $childItem->setSku($replacementProduct['sku']);
            $productOptions = $childItem->getProductOptions();
            $productOptions['info_buyRequest']['super_attribute'][$sizeAttribute->getId()] = $replacementProduct['size'];
            $childItem->setProductOptions($productOptions);
            $childItem->save();

            // Y el parent
            $parentItem->setSku($replacementProduct['sku']);
            $productOptions = $parentItem->getProductOptions();
            $productOptions['simple_sku'] = $replacementProduct['sku'];
            $productOptions['info_buyRequest']['super_attribute'][$sizeAttribute->getId()] = $replacementProduct['size'];
            $productOptions['attributes_info'][0]['value'] = $replacementProduct->getAttributeText('size');
            $parentItem->setProductOptions($productOptions);
            $parentItem->save();

            // Actualizamos los stocks; le volvemos a sumar la cantidad al producto
            // pedido originalmente y se la restamos al producto de reemplazo
            $qty = $parentItem->getQtyOrdered();

            /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
            $stockItem = $previousProduct['stock_item'];
            $stockItem->setManageStock(1);
            $stockItem->setQty($stockItem->getQty() + $qty);
            $stockItem->save();

            $stockItem = $replacementProduct['stock_item'];
            $stockItem->setManageStock(1);
            $stockItem->setQty($stockItem->getQty() - $qty);
            $stockItem->save();
        }
    }

    private function cancelOrder(Webkul_RmaSystem_Model_Rma $rma)
    {
        $this->log('Iniciando la devolución...');

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($rma['order_id']);

        if(!$order->hasInvoices())
        {
            Mage::getModel('rmasystem/ordercancel_uninvoicedOrder')
                ->load($order, $rma)
                ->cancel();
        }
        else
        {
            Mage::getModel('rmasystem/ordercancel_invoicedOrder')
                ->load($order, $rma)
                ->cancel();
        }
    }

    /**
     * @param Mage_Sales_Model_Order_Item $parentItem
     * @param $orderItems
     * @return Mage_Sales_Model_Order_Item|null
     */
    private static function getAssociatedSimpleItem(Mage_Sales_Model_Order_Item $parentItem, $orderItems)
    {
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        foreach($orderItems as $orderItem)
        {
            if($orderItem->getParentItemId() == $parentItem->getId())
            {
                return $orderItem;
            }
        }

        return null;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param DateTime $date
     */
    public function generatePickupLabel(
        Mage_Sales_Model_Order $order,
        DateTime $date)
    {

        Mage::getModel('rmasystem/pickupLabels_seurWebService')
            ->generateLabel($order, $date);
    }

    /**
     * @param $message
     * @param array $args
     */
    private function log($message, $args = [])
    {
        return Mage::helper('rmasystem')->log($message, $args);
    }
}
