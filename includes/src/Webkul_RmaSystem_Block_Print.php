<?php

	class Webkul_RmaSystem_Block_Print extends Mage_Customer_Block_Account	{

		public function __construct()    {
            parent::__construct();
            $id = $this->getRequest()->getParam("id");
            $collection = Mage::getResourceModel("rmasystem/conversation_collection")->addFieldToFilter("rma_id",$id);
            $this->setCollection($collection);
        }

	}