<?php

class Webkul_RmaSystem_Model_Refund_PayPal
{
    /**
     * @param $transactionId
     * @param $partialRefund
     * @param $refundAmount
     * @return Webkul_RmaSystem_Model_Refund_PayPalRefundTransactionResult
     * @throws Exception
     */
    public function refundTransaction($transactionId, $partialRefund = false, $refundAmount = null)
    {
        // Obtenemos las credenciales de PayPal
        $apiUsername = Mage::getStoreConfig('paypal/wpp/api_username');
        $apiPassword = Mage::getStoreConfig('paypal/wpp/api_password');
        $apiSignature = Mage::getStoreConfig('paypal/wpp/api_signature');
        $isSandbox = Mage::getStoreConfig('paypal/wpp/sandbox_flag');

        $url = $isSandbox
            ? 'https://api-3t.sandbox.paypal.com/nvp'
            : 'https://api-3t.paypal.com/nvp';

        // Preparamos los parámetros
        $postData = [
            'USER'          => $apiUsername,
            'PWD'           => $apiPassword,
            'SIGNATURE'     => $apiSignature,
            'METHOD'        => 'RefundTransaction',
            'VERSION'       => 94,
            'TRANSACTIONID' => $transactionId,
            'REFUNDTYPE'    => 'Full'
        ];

        // Si el tipo de devolución es parcial, ajustamos un par de parámetros
        if($partialRefund)
        {
            $postData['REFUNDTYPE'] = 'Partial';
            $postData['AMT'] = $refundAmount;
        }

        $connection = curl_init($url);
        curl_setopt($connection, CURLOPT_POST, true);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connection, CURLOPT_POSTFIELDS, http_build_query($postData));

        $this->log('Iniciando petición de reembolso...');
        $this->log('Llamando a %s con los parámetros: %s', [$url, json_encode($postData)]);
        $response = curl_exec($connection);
        $this->log('Respuesta obtenida: %s', $response);

        // Parseamos la respuesta
        parse_str($response, $responseData);

        if($responseData['ACK'] === 'Success')
        {
            // La devolución se ha realizado correctamente
            $refundTransactionId = $response['REFUNDTRANSACTIONID'];
            $this->log('El reembolso se ha realizado correctamente.');
            return Mage::getModel('rmasystem/refund_payPalRefundTransactionResult')
                ->load(true, $refundTransactionId);
        }
        else
        {
            // Se ha producido un error
            $errorMessage = $responseData['L_LONGMESSAGE0'];
            $this->log('No se ha podido realizar el reembolso: %s', $errorMessage);
            return Mage::getModel('rmasystem/refund_payPalRefundTransactionResult')
                ->load(false, null, $errorMessage);
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