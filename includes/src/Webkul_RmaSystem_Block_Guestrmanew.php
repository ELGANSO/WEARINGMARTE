<?php

	class Webkul_RmaSystem_Block_Guestrmanew extends Mage_Core_Block_Template	{

		public function __construct()    {
            parent::__construct();
                $session_data = Mage::getSingleton("core/session")->getGuestData();
                $allowed_status = Mage::getStoreConfig("rmasystem/rmasystem/allowed-order-status");
                if($allowed_status == "complete")
                {
                    $array_of_order_id=array();
                    $order_collection = Mage::getModel("sales/order")->getCollection()->addFieldToFilter("customer_email",$session_data["email"]);
                    foreach ($order_collection as $value) {
                        $array_of_order_id[]=$value->getEntityId();
                    }

                    $collection = Mage::getModel("sales/order_shipment")->getCollection();
                    $collection->join(array("so" => "sales/order"),"so.entity_id=main_table.order_id",array("grand_total","increment_id","created_at"), null,"left");
                    $collection->addFilterToMap("created_at","so.created_at");
                    $collection->addFilterToMap("customer_id","so.customer_id");
                    $collection->addFilterToMap("increment_id","so.increment_id");
                    $collection->addFieldToFilter('order_id',array('in'=>$array_of_order_id));

                    
                }
                else
                    $collection = Mage::getModel("sales/order")->getCollection()->addFieldToFilter("customer_email",$session_data["email"]);

                $allowed_days = Mage::getStoreConfig("rmasystem/rmasystem/valid-days",Mage::app()->getStore());
                if($allowed_days != ""){
                    $todays_second = time();
                    $allowed_seconds = $allowed_days*86400;
                    $past_second_from_today = $todays_second - $allowed_seconds;
                    $valid_from = date("Y-m-d H:i:s",$past_second_from_today);
                    $collection->addFieldToFilter("created_at",array("gteq" => $valid_from));
                }
                $filter_data = Mage::getSingleton("core/session")->getGuestFilterData();
                if($filter_data["order_id"] != "")
                    $collection->addFieldToFilter("increment_id",$filter_data["order_id"]);
                if($filter_data["date"] != "")
                    $collection->addFieldToFilter("created_at",array("gt" => $filter_data["date"]." 23:59:59"));
                if($filter_data["price"] != "")
                    $collection->addFieldToFilter("grand_total",array("gteq" => $filter_data["price"]));
                $collection->setOrder('increment_id','ASC');
                $sorting_data = Mage::getSingleton("core/session")->getGuestSortingData();
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