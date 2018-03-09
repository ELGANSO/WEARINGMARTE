<?php

class Ontic_SpecialPriceTime_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }
        // unix timestamp given - simply instantiate date object
        if (preg_match('/^[0-9]+$/', $date)) {
            $date = new Zend_Date((int)$date);
        }
        // international format
        else if (preg_match('#^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$#', $date)) {
            $zendDate = new Zend_Date();
            $date = $zendDate->setIso($date);
        }
        // parse this date in current locale, do not apply GMT offset
        else {
            $date = Mage::app()->getLocale()->date($date,
                Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT . ' HH:mm:ss'),
                null, false
            );
        }
        return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }
}