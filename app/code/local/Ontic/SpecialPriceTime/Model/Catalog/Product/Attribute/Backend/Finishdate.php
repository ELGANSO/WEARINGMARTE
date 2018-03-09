<?php

class Ontic_SpecialPriceTime_Model_Catalog_Product_Attribute_Backend_Finishdate
    extends Innoexts_StorePricing_Model_Catalog_Product_Attribute_Backend_Finishdate
{
    public function formatDate($date)
    {
        return Mage::helper('ontic_specialpricetime')->formatDate($date);
    }
}