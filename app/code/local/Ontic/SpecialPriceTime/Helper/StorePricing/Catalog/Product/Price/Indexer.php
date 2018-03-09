<?php

class Ontic_SpecialPriceTime_Helper_StorePricing_Catalog_Product_Price_Indexer extends Innoexts_StorePricing_Helper_Catalog_Product_Price_Indexer
{
    public function getFinalPriceExpr($write, $price, $specialPrice, $specialFrom, $specialTo)
    {
        if ($this->getVersionHelper()->isGe1600()) {
            $timeZone = Mage::getStoreConfig('general/locale/timezone');
            $currentDate = new Zend_Db_Expr((new DateTime('now', new DateTimeZone($timeZone)))->format('\'Y-m-d-H-i-s\''));
            if ($this->getVersionHelper()->isGe1700()) {
                $groupPrice     = $write->getCheckSql('gp.price IS NULL', "{$price}", 'gp.price');
            }
            $specialFromDate    = $write->getDateFormatSql($specialFrom, '%Y-%m-%d-%H-%i-%s');
            $specialToDate      = $write->getDateFormatSql($specialTo, '%Y-%m-%d-%H-%i-%s');
            $specialFromUse     = $write->getCheckSql("{$specialFromDate} <= {$currentDate}", '1', '0');
            $specialToUse       = $write->getCheckSql("{$specialToDate} >= {$currentDate}", '1', '0');
            $specialFromHas     = $write->getCheckSql("{$specialFrom} IS NULL", '1', "{$specialFromUse}");
            $specialToHas       = $write->getCheckSql("{$specialTo} IS NULL", '1', "{$specialToUse}");
            $finalPrice         = $write->getCheckSql("{$specialFromHas} > 0 AND {$specialToHas} > 0"
                . " AND {$specialPrice} < {$price}", $specialPrice, $price);
            if ($this->getVersionHelper()->isGe1700()) {
                $finalPrice         = $write->getCheckSql("{$groupPrice} < {$finalPrice}", $groupPrice, $finalPrice);
            }
        } else {
            $currentDate    = new Zend_Db_Expr('cwd.date');
            $finalPrice     = new Zend_Db_Expr("IF(IF({$specialFrom} IS NULL, 1, "
                . "IF(DATE({$specialFrom}) <= {$currentDate}, 1, 0)) > 0 AND IF({$specialTo} IS NULL, 1, "
                . "IF(DATE({$specialTo}) >= {$currentDate}, 1, 0)) > 0 AND {$specialPrice} < {$price}, "
                . "{$specialPrice}, {$price})");
        }
        return $finalPrice;
    }
}