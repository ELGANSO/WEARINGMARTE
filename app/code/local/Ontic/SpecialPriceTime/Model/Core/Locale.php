<?php

class Ontic_SpecialPriceTime_Model_Core_Locale extends Mage_Core_Model_Locale
{
    public function isStoreDateInInterval($store, $dateFrom = null, $dateTo = null)
    {
        if (!$store instanceof Mage_Core_Model_Store) {
            $store = Mage::app()->getStore($store);
        }

        $storeTimeStamp = $this->storeTimeStamp($store);
        $fromTimeStamp  = strtotime($dateFrom);
        $toTimeStamp    = strtotime($dateTo);

        $result = false;
        if (!is_empty_date($dateFrom) && $storeTimeStamp < $fromTimeStamp) {
        }
        elseif (!is_empty_date($dateTo) && $storeTimeStamp > $toTimeStamp) {
        }
        else {
            $result = true;
        }

        return $result;
    }
}