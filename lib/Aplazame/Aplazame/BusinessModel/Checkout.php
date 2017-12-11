<?php

/**
 * Checkout.
 */
class Aplazame_Aplazame_BusinessModel_Checkout
{
    public static function createFromOrder(Mage_Sales_Model_Order $order)
    {
        $merchant = new stdClass();
        $merchant->confirmation_url = Mage::getUrl("aplazame/payment/confirm", array('_secure' => true));
        $merchant->cancel_url = Mage::getUrl('aplazame/payment/cancel', array('_secure' => true));
        $merchant->success_url = Mage::getUrl('checkout/onepage/success', array('_secure' => true));
        $merchant->checkout_url = Mage::getUrl('aplazame/payment/cart');

        $checkout = new self();
        $checkout->toc = true;
        $checkout->merchant = $merchant;
        $checkout->order = Aplazame_Aplazame_BusinessModel_Order::crateFromOrder($order);
        $checkout->customer = Aplazame_Aplazame_BusinessModel_Customer::createFromOrder($order);
        $checkout->billing = Aplazame_Aplazame_BusinessModel_Address::createFromAddress($order->getBillingAddress());
        $checkout->shipping = Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromOrder($order);
        $checkout->meta = array(
            "module" => array(
                "name" => "aplazame:magento",
                "version" => Mage::getVersion(),
            ),
            "version" => Mage::getConfig()->getModuleConfig('Aplazame_Aplazame')->version,
        );

        return $checkout;
    }
}
