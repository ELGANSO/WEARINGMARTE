<?php

class Webkul_RmaSystem_Model_Items extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('rmasystem/items');
    }

    /**
     * @return int
     */
    public function getItemId()
    {
        return $this['item_id'];
    }

    /**
     * @return Mage_Core_Model_Abstract|Mage_Sales_Model_Order_Item
     */
    public function getOrderItem()
    {
        return Mage::getModel('sales/order_item')->load($this->getItemId());
    }

    /**
     * @return Mage_Core_Model_Abstract|Mage_Sales_Model_Order_Item
     */
    public function getSimpleOrderItem()
    {
        $orderItem = $this->getOrderItem();
        if($simpleItem = Mage::helper('rmasystem')->getLinkedSimpleItem($orderItem))
        {
            return $simpleItem;
        }
        return $orderItem;
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return $this['qty'];
    }

    /**
     * @return string
     */
    public function getRequestedProductSize()
    {
        return $this['requested_product_size'];
    }

    /**
     * @return int
     */
    public function getRequestedProductId()
    {
        return $this['requested_product_id'];
    }

    /**
     * @return Mage_Catalog_Model_Product|Mage_Core_Model_Abstract
     */
    public function getRequestedProduct()
    {
        return Mage::getModel('catalog/product')->load($this->getRequestedProductId());
    }
}