<?php

class Webkul_RmaSystem_Model_Ordercancel_InvoicedOrder
{
    /** @var Mage_Sales_Model_Order */
    private $order;
    /** @var Webkul_RmaSystem_Model_Rma */
    private $rma;
    /** @var  Webkul_RmaSystem_Model_Items[] */
    private $rmaItems;

    /**
     * @param Mage_Sales_Model_Order $order
     * @param Webkul_RmaSystem_Model_Rma $rma
     * @return Webkul_RmaSystem_Model_Ordercancel_InvoicedOrder
     */
    public function load(Mage_Sales_Model_Order $order, Webkul_RmaSystem_Model_Rma $rma)
    {
        $this->order = $order;
        $this->rma = $rma;

        // Cargamos los items del RMA
        $this->rmaItems = Mage::getModel('rmasystem/items')
            ->getCollection()
            ->addFieldToFilter('rma_id', $rma->getId());

        return $this;
    }

    public function cancel()
    {
        static::log('Emitiendo una factura de abono para el pedido asociado %d...', $this->order->getId());

        // Emitimos una factura de abono para los productos devueltos
        $creditMemo = $this->generateCreditMemo($this->order, $this->rmaItems);

        // Restauramos el stock de los productos
        static::restockProducts($this->rmaItems);

        // Realizamos la devolución
        static::refundPayment($this->order, $creditMemo);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param Webkul_RmaSystem_Model_Items[] $rmaItems
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    private static function generateCreditMemo(
        Mage_Sales_Model_Order $order,
        $rmaItems)
    {
        $data = ['qtys' => []];
        foreach ($rmaItems as $rmaItem)
        {
            $data['qtys'][$rmaItem->getItemId()] = $rmaItem->getQty();
        }

        /** @var Mage_Sales_Model_Service_Order $service */
        $service = Mage::getModel('sales/service_order', $order);
        $creditMemo = $service->prepareCreditmemo($data)->register()->save();
        $order->save();

        return $creditMemo;
    }

    /**
     * @param Webkul_RmaSystem_Model_Items[] $rmaItems
     */
    private static function restockProducts($rmaItems)
    {
        $helper = Mage::helper('rmasystem');

        foreach($rmaItems as $rmaItem)
        {
            $orderItem = $rmaItem->getOrderItem();
            $simpleItem = $helper->getLinkedSimpleItem($orderItem);
            $item = ($simpleItem == null)
                ? $orderItem
                : $simpleItem;

            $helper->restoreStock($item->getProductId(), $rmaItem->getQty());
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Creditmemo $creditMemo
     */
    private static function refundPayment(
        Mage_Sales_Model_Order $order,
        Mage_Sales_Model_Order_Creditmemo $creditMemo)
    {
        if($creditMemo->getGrandTotal() == $order->getGrandTotal())
        {
            // Se trata de una devolución total
            $partialRefund = false;
            $amountRefunded = null;
        }
        else
        {
            // Devolución parcial
            $partialRefund = true;
            $amountRefunded = $creditMemo->getGrandTotal();
        }

        $payment = $order->getPayment();
        $method = $payment->getMethod();
        static::log('Devolviendo el pago realizado mediante "%s"...', $method);
        if($method === 'paypal_express')
        {
            $transactionId = $payment->getLastTransId();
            static::log('El pedido fue pagado mediante PayPal, iniciando el reembolso de la transacción %s...', $transactionId);


            $response = Mage::getModel('rmasystem/refund_payPal')->refundTransaction($transactionId, $partialRefund, $amountRefunded);
            if($response->isSuccess())
            {
                // Guardamos el ID de la transacción del reembolso en la sección de datos adicionales del pago
                $payment->setAdditionalInformation('paypal_refund_transaction_id', $response->getRefundTransactionId())->save();
            }
            else
            {
                // Mostramos un aviso al admin
                Mage::getSingleton('adminhtml/session')
                    ->addWarning('No se ha podido devolver el pago por PayPal: ' . $response->getErrorMessage());
            }
        }
        else if($method === 'i4redsyspro')
        {
            static::log('El pedido fue pagado mediante TPV, iniciando el reembolso...');
            $response = Mage::getModel('rmasystem/refund_redsys')->refundTransaction($order, $partialRefund, $amountRefunded);
            if($response === false)
            {
                // Mostramos un aviso al admin
                Mage::getSingleton('adminhtml/session')
                    ->addWarning('No se ha podido devolver el pago por TPV.');
            }
        }
        else
        {
            static::log('Método de pago desconocido, no se puede reembolsar automáticamente.');
        }
    }

    /**
     * @param string $message
     * @param array $args
     */
    private static function log($message, $args = [])
    {
        return Mage::helper('rmasystem')->log($message, $args);
    }
}