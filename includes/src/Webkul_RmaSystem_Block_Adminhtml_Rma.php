<?php

class Webkul_RmaSystem_Block_Adminhtml_Rma extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = "adminhtml_rma";
        $this->_blockGroup = "rmasystem";
        $this->_headerText = $this->__("All RMA Request");
        parent::__construct();
        $this->_removeButton('add');
    }

    public function getStatus()
    {
        return $this['status'];
    }
}