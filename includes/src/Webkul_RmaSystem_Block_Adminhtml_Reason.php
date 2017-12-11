<?php

	class Webkul_RmaSystem_Block_Adminhtml_Reason extends Mage_Adminhtml_Block_Widget_Grid_Container {

	    public function __construct() {
	        $this->_controller = "adminhtml_reason";
	        $this->_blockGroup = "rmasystem";
	        $this->_headerText = $this->__("RMA Reasons");
	        parent::__construct();
	    }

	}