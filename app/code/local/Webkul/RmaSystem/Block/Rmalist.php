<?php

	class Webkul_RmaSystem_Block_Rmalist extends Mage_Core_Block_Template	{

		public function __construct()    {
            parent::__construct();
            $customerid = Mage::getSingleton("customer/session")->getCustomerId();
            $collection = Mage::getResourceModel("rmasystem/rma_collection")->addFieldToFilter("customer_id",$customerid)->setOrder("id","DESC");

            $filter_data = Mage::getSingleton("customer/session")->getRmaFilterData();
            if($filter_data["order_id"] != "")
                $collection->addFieldToFilter("order_id",$filter_data["order_id"]);
            if($filter_data["status"] != "")
                $collection->addFieldToFilter("status",$filter_data["status"]);
            if($filter_data["rma_id"] != "")
                $collection->addFieldToFilter("id",$filter_data["rma_id"]);
            if($filter_data["date"] != "")
                $collection->addFieldToFilter("created_at",array("gt" => $filter_data["date"]." 23:59:59"));

            $sorting_data = Mage::getSingleton("customer/session")->getRmaSortingData();
            if($sorting_data["attr"] != "" && $sorting_data["direction"] != "")
                $collection->setOrder($sorting_data["attr"],$sorting_data["direction"]);
            $this->setCollection($collection);
        }

        protected function _prepareLayout()    {
            parent::_prepareLayout(); 
            $pager = $this->getLayout()->createBlock("page/html_pager","custom.pager");
            $pager->setAvailableLimit(array(9=>9,15=>15,30=>30,"all"=>"all"));
            $pager->setCollection($this->getCollection());
            $this->setChild("pager",$pager);
            $this->getCollection()->load();
            return $this;
        }

        public function getPagerHtml()   {
            return $this->getChildHtml("pager");
        }

	}