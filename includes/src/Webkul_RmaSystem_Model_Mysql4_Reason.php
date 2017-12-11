<?php

    class Webkul_RmaSystem_Model_Mysql4_Reason extends Mage_Core_Model_Mysql4_Abstract    {

        public function _construct()   {
            $this->_init("rmasystem/reason","id");
        }

    }