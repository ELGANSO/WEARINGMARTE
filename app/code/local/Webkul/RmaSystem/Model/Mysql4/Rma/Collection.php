<?php

    class Webkul_RmaSystem_Model_Mysql4_Rma_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract    {

        public function _construct()     {
            parent::_construct();
            $this->_init("rmasystem/rma");
        }

    }