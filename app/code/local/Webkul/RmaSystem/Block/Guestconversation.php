<?php

	class Webkul_RmaSystem_Block_Guestconversation extends Mage_Customer_Block_Account	{

		public function __construct()    {
            parent::__construct();
    		$id = $this->getRequest()->getParam("id");
            $collection = Mage::getResourceModel("rmasystem/conversation_collection")->addFieldToFilter("rma_id",$id);
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

        public function getRmaDetails($id){
            $model = Mage::getModel("rmasystem/rma")->load($id);
            $reason_model = Mage::getModel("rmasystem/reason")->load($model->getReason());
            $model->setReason($reason_model->getReason());
            return $model;
        }

	}