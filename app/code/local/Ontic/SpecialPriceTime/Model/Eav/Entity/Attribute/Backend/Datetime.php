<?php

class Ontic_SpecialPriceTime_Model_Eav_Entity_Attribute_Backend_Datetime
    extends Mage_Eav_Model_Entity_Attribute_Backend_Datetime
{
    public function formatDate($date)
    {
        return Mage::helper('ontic_specialpricetime')->formatDate($date);
    }
}