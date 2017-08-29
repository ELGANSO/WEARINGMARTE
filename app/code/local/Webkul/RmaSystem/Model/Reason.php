<?php

    class Webkul_RmaSystem_Model_Reason extends Mage_Core_Model_Abstract    {

        public function _construct()    {
            parent::_construct();
            $this->_init("rmasystem/reason");
        }

    }