<?php

class Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder
{
    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public static function createFromOrder(Mage_Sales_Model_Order $order)
    {
        $serialized = array(
            'id' => $order->getIncrementId(),
            'amount' => Aplazame_Sdk_Serializer_Decimal::fromFloat($order->getGrandTotal()),
            'due' => Aplazame_Sdk_Serializer_Decimal::fromFloat($order->getTotalDue()),
            'status' => $order->getStatus(),
            'type' => $order->getPayment()->getMethodInstance()->getCode(),
            'order_date' => date(DATE_ISO8601, strtotime($order->getCreatedAt())),
            'currency' => $order->getOrderCurrencyCode(),
            'billing' => Aplazame_Aplazame_BusinessModel_Address::createFromAddress($order->getBillingAddress()),
            'shipping' => Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromOrder($order),
        );

        return $serialized;
    }
}
