<?php

	class Webkul_RmaSystem_Block_Adminhtml_Label extends Mage_Adminhtml_Block_Widget_Grid_Container {

	    public function __construct() {
	        $this->_controller = "adminhtml_label";
	        $this->_blockGroup = "rmasystem";
	        $this->_headerText = $this->__("Shipping Label");
	        $this->_addButtonLabel = $this->__("Add Label");
	        parent::__construct();
	    }

	}