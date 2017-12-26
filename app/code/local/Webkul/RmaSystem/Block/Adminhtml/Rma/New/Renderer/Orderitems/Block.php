<?php
class Webkul_RmaSystem_Block_Adminhtml_Rma_New_Renderer_Orderitems_Block extends Mage_Core_Block_Template
{
    /** @var int */
    private $orderId;
    /** @var Mage_Sales_Model_Order */
    private $order;
    /** @var Webkul_RmaSystem_Helper_Data */
    private $helper;
    /**
     * Webkul_RmaSystem_Block_Adminhtml_Rma_New_Renderer_Orderitems_Block constructor.
     * @param array $args
     */
    function __construct(array $args)
    {
        parent::__construct($args);
        $this->helper = Mage::helper('rmasystem');
    }
    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }
    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }
    /**
     * @return Mage_Sales_Model_Order_Item[]
     */
    public function getOrderItems()
    {
        $items = [];
        $helper = Mage::helper('rmasystem');
        /** @var Mage_Sales_Model_Order_Item $item */
        foreach($this->getOrder()->getAllVisibleItems() as $item)
        {
            if($helper->orderItemQuailifiesForRma($item))
            {
                $items[] = $item;
            }
        }
        return $items;
    }
    /**
     * Devuelve el número de unidades de una línea de pedido de
     * las que se puede solicitar devolución
     * @param Mage_Sales_Model_Order_Item $item
     * @return int
     */
    public function getQtyAvailableForRefund(Mage_Sales_Model_Order_Item $item)
    {
        return $this->helper->getQtyAvailableForRefund($item);
    }
    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @return null|string
     */
    public function getItemSize(Mage_Sales_Model_Order_Item $item)
    {
        if($childItem = $this->helper->getLinkedSimpleItem($item))
        {
            /** @noinspection PhpParamsInspection */
            return $childItem->getProduct()->getAttributeText('size');
        }
        return null;
    }
    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @return string
     */
    public function getFormattedPrice(Mage_Sales_Model_Order_Item $item)
    {
        return Mage::helper('core')->formatCurrency($item->getBasePriceInclTax() - $item->getBaseDiscountAmount());
    }
    /**
     * @return Mage_Sales_Model_Order
     */
    private function getOrder()
    {
        if($this->order === null)
        {
            $this->order = Mage::getModel('sales/order')->load($this->orderId);
        }
        return $this->order;
    }
    /**
     * @return Webkul_RmaSystem_Helper_Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
