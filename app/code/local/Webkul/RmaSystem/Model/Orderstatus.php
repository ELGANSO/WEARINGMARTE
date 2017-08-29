<?php

	class Webkul_RmaSystem_Model_Orderstatus	{

		public function toOptionArray()	{
			return array(
				array("value" => "complete", "label" => Mage::helper("rmasystem")->__("Complete")),
				array("value" => "all", "label" => Mage::helper("rmasystem")->__("All Status"))
			);
		}

	}