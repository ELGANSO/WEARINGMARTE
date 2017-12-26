<?php
class Webkul_RmaSystem_Block_Rmanew extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $customer_id = Mage::getSingleton("customer/session")->getCustomer()->getId();
        $allowed_status = Mage::getStoreConfig("rmasystem/rmasystem/allowed-order-status");
        if ($allowed_status == "complete")
        {
            /** @var Mage_Sales_Model_Entity_Order_Shipment_Collection $collection */
            $collection = Mage::getModel("sales/order_shipment")->getCollection();
            $collection->join(array("so" => "sales/order"), "so.entity_id=main_table.order_id", array("grand_total", "increment_id", "created_at"), null, "left");
            $collection->addFilterToMap("created_at", "so.created_at");
            $collection->addFilterToMap("customer_id", "so.customer_id");
            $collection->addFilterToMap("increment_id", "so.increment_id");
            $collection->addFieldToFilter("customer_id", $customer_id);
        } else
            $collection = Mage::getModel("sales/order")->getCollection()->addFieldToFilter("customer_id", $customer_id);
        $allowed_days = Mage::getStoreConfig("rmasystem/rmasystem/valid-days", Mage::app()->getStore());
        if ($allowed_days != "")
        {
            $todays_second = time();
            $allowed_seconds = $allowed_days * 86400;
            $past_second_from_today = $todays_second - $allowed_seconds;
            $valid_from = date("Y-m-d H:i:s", $past_second_from_today);
            $collection->addFieldToFilter("created_at", array("gteq" => $valid_from));
        }
        $filter_data = Mage::getSingleton("customer/session")->getFilterData();
        if ($filter_data["order_id"] != "")
            $collection->addFieldToFilter("increment_id", $filter_data["order_id"]);
        if ($filter_data["date"] != "")
            $collection->addFieldToFilter("created_at", array("gt" => $filter_data["date"] . " 23:59:59"));
        if ($filter_data["price"] != "")
            $collection->addFieldToFilter("grand_total", array("gteq" => $filter_data["price"]));
        $collection->setOrder('increment_id', 'ASC');
        $sorting_data = Mage::getSingleton("customer/session")->getSortingData();
        if ($sorting_data["attr"] != "" && $sorting_data["direction"] != "")
            $collection->setOrder($sorting_data["attr"], $sorting_data["direction"]);
        // Evitamos mostrar pedidos cuyos todos sus productos estén ya implicados
        // en una devolución abierta
        $collection->getSelect()->where(sprintf(
            '
                (SELECT COALESCE(SUM(qty_ordered - qty_refunded), 0)
                FROM sales_flat_order_item
                WHERE order_id = main_table.entity_id AND parent_item_id IS NULL) 
                > 
                (SELECT COALESCE(SUM(qty), 0)
                FROM wk_rma_items
                INNER JOIN wk_rma ON wk_rma_items.rma_id = wk_rma.id AND status NOT IN (%s, %s, %s)
                WHERE wk_rma.order_id = main_table.entity_id)',
            Webkul_RmaSystem_Model_Constants::StatusAccepted,
            Webkul_RmaSystem_Model_Constants::StatusDenied,
            Webkul_RmaSystem_Model_Constants::StatusCancelled
        ));
        $this->setCollection($collection);
    }
    protected function _prepareLayout()
    {
        $this->_injectCalendarControlJsCSSInHTMLPageHead();
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock("page/html_pager", "custom.pager");
        $pager->setAvailableLimit(array(9 => 9, 15 => 15, 30 => 30, "all" => "all"));
        $pager->setCollection($this->getCollection());
        $this->setChild("pager", $pager);
        $this->getCollection()->load();
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml("pager");
    }
    /**
     * @return string
     */
    public function getDateFormat()
    {
        $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) ;
        $dateFormat = Varien_Date::convertZendToStrftime($dateFormat, true, false);
        return $dateFormat;
    }
    // http://inchoo.net/magento/reusing-magento-calendar-control/
    private function _injectCalendarControlJsCSSInHTMLPageHead()
    {
        $this->getLayout()->getBlock('head')->append(
            $this->getLayout()->createBlock(
                'Mage_Core_Block_Html_Calendar',
                'html_calendar',
                array('template' => 'page/js/calendar.phtml')
            )
        );
        $this->getLayout()->getBlock('head')
            ->addItem('js_css', 'calendar/calendar-win2k-1.css')
            ->addJs('calendar/calendar.js')
            ->addJs('calendar/calendar-setup.js');
        return $this;
    }
}