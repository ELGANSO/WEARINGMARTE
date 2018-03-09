<?php

class Ontic_SpecialPriceTime_Model_Catalog_Product_Attribute_Backend_Startdate_Specialprice
    extends Mage_Catalog_Model_Product_Attribute_Backend_Startdate_Specialprice
{
    public function formatDate($date)
    {
        return Mage::helper('ontic_specialpricetime')->formatDate($date);
    }
}