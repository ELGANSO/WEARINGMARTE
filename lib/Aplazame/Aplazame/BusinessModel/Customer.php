<?php

/**
 * Customer.
 */
class Aplazame_Aplazame_BusinessModel_Customer
{
    public static function createFromOrder(Mage_Sales_Model_Order $order)
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer->getId()) {
            return self::createFromCustomer($customer);
        }

        return self::createGuessCustomerFromOrder($order);
    }

    public static function createFromCustomer(Mage_Customer_Model_Customer $customer)
    {
        /** @var Mage_Log_Model_Customer $logCustomer */
        $logCustomer = Mage::getModel('log/customer')->loadByCustomer($customer->getId());

        switch ($customer->getGender()) {
            case '1':
                $gender = 1;
                break;
            case '2':
                $gender = 2;
                break;
            default:
                $gender = 0;
        }

        $aCustomer = new self();
        $aCustomer->email = $customer->getEmail();
        $aCustomer->type = 'e';
        $aCustomer->gender = $gender;
        $aCustomer->id = $customer->getId();
        $aCustomer->first_name = $customer->getFirstname();
        $aCustomer->last_name = $customer->getLastname();
        if (($birthday = $customer->getDob()) !== null) {
            $aCustomer->birthday = Aplazame_Sdk_Serializer_Date::fromDateTime(new DateTime($birthday));
        }
        if (($document_id = $customer->getTaxvat()) !== null) {
            $aCustomer->document_id = $document_id;
        }
        $aCustomer->date_joined = Aplazame_Sdk_Serializer_Date::fromDateTime(new DateTime('@' . $customer->getCreatedAtTimestamp()));
        if (($lastLogin = $logCustomer->getLoginAtTimestamp()) != null) {
            $aCustomer->last_login = Aplazame_Sdk_Serializer_Date::fromDateTime(new DateTime('@' . $lastLogin));
        }

        return $aCustomer;
    }

    public static function createGuessCustomerFromOrder(Mage_Sales_Model_Order $order)
    {
        $aCustomer = new self();
        $aCustomer->email = $order->getCustomerEmail();
        $aCustomer->type = 'g';
        $aCustomer->gender = 0;
        $aCustomer->first_name = $order->getCustomerFirstname();
        $aCustomer->last_name = $order->getCustomerLastname();

        return $aCustomer;
    }
}
