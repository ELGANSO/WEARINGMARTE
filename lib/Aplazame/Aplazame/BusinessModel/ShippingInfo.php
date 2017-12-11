<?php

/**
 * Shipping info.
 */
class Aplazame_Aplazame_BusinessModel_ShippingInfo
{
    public static function createFromOrder(Mage_Sales_Model_Order $order)
    {
        $address = $order->getShippingAddress();

        $shippingInfo = new self();
        $shippingInfo->first_name = $address->getFirstname();
        $shippingInfo->last_name = $address->getLastname();
        $shippingInfo->street = $address->getStreet(1);
        $shippingInfo->city = $address->getCity();
        $shippingInfo->state = $address->getRegion();
        $shippingInfo->country = $address->getCountryModel()->getIso2Code();
        $shippingInfo->postcode = $address->getPostcode();
        $shippingInfo->name = $order->getShippingMethod();
        $shippingInfo->price = Aplazame_Sdk_Serializer_Decimal::fromFloat($order->getShippingAmount());
        $shippingInfo->phone = $address->getTelephone();
        $shippingInfo->alt_phone = $address->getAltTelephone();
        $shippingInfo->address_addition = $address->getStreet(2);
        if ($order->getShippingAmount() > 0) {
            $shippingInfo->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat(100 * $order->getShippingTaxAmount() / $order->getShippingAmount());
        }
        $shippingInfo->discount = Aplazame_Sdk_Serializer_Decimal::fromFloat($order->getShippingDiscountAmount());

        return $shippingInfo;
    }
}
