<?php

class Webkul_RmaSystem_Model_Refund_Redsys
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function refundTransaction(Mage_Sales_Model_Order $order, $partialRefund = false, $amount = null)
    {
        if($partialRefund === false && $amount === null)
        {
            $amount = (int)$order->getGrandTotal() * 100;
        }
        else
        {
            $amount = (int)$amount * 100;
        }

        $orderId = Mage::helper('i4redsyspro')->generateRedsysOrderReference($order);
        $authorisationCode = $order->getPayment()->getLastTransId();
        $merchantCode = Mage::getStoreConfig('payment/i4redsyspro/merchantnumber');
        $terminal = Mage::getStoreConfig('payment/i4redsyspro/merchantterminal');
        $key = Mage::helper('i4redsyspro')->isProductionEnvironment()
            ? Mage::getStoreConfig('payment/i4redsyspro/merchantpassword256')
            : Mage::getStoreConfig('payment/i4redsyspro/devpassword256');
        $url = Mage::helper('i4redsyspro')->getRedsysUrl();

        $redsys = new RedsysAPI();
        $redsys->setParameter('DS_MERCHANT_AMOUNT', $amount);
        $redsys->setParameter('DS_MERCHANT_ORDER', $orderId);
        $redsys->setParameter('DS_MERCHANT_MERCHANTCODE', $merchantCode);
        $redsys->setParameter('DS_MERCHANT_TERMINAL', $terminal);
        $redsys->setParameter('DS_MERCHANT_CURRENCY', '978');
        $redsys->setParameter('DS_MERCHANT_TRANSACTIONTYPE', '3');
        $redsys->setParameter('DS_MERCHANT_AUTHORISATIONCODE', $authorisationCode);
        $redsys->setParameter('DS_MERCHANT_MERCHANTURL', '');
        $redsys->setParameter('DS_MERCHANT_URLOK', '');
        $redsys->setParameter('DS_MERCHANT_URLKO', '');
        $signature = $redsys->createMerchantSignature($key);

        $postData = [
            'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
            'Ds_MerchantParameters' => $redsys->createMerchantParameters(),
            'Ds_Signature' => $signature
        ];

        $connection = curl_init($url);
        curl_setopt($connection, CURLOPT_POST, true);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connection, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($connection ,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $this->log('Iniciando petición de reembolso...');
        $response = curl_exec($connection);
        $this->log('Respuesta obtenida: %s', $response);

        if(strpos($response, 'operacionAceptada') !== false)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @param string $message
     * @param array $args
     */
    private function log($message, $args = [])
    {
        return Mage::helper('rmasystem')->log($message, $args);
    }
}