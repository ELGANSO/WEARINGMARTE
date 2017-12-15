<?php

class Aplazame_Aplazame_BusinessModel_Address
{
    public static function createFromAddress(Mage_Sales_Model_Order_Address $address)
    {
        $aAddress = new self();
        $aAddress->first_name = $address->getFirstname();
        $aAddress->last_name = $address->getLastname();
        $aAddress->street = $address->getStreet(1);
        $aAddress->city = $address->getCity();
        $aAddress->state = $address->getRegion();
        $aAddress->country = $address->getCountryModel()->getIso2Code();
        $aAddress->postcode = $address->getPostcode();
        $aAddress->phone = $address->getTelephone();
        $aAddress->alt_phone = $address->getAltTelephone();
        $aAddress->address_addition = $address->getStreet(2);

        return $aAddress;
    }
}
