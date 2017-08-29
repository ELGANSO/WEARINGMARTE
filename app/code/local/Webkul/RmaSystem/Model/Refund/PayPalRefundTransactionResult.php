<?php

class Webkul_RmaSystem_Model_Refund_PayPalRefundTransactionResult
{
    /** @var bool */
    private $success;
    /** @var string|null */
    private $refundTransactionId;
    /** @var string|null */
    private $errorMessage;

    /**
     * @param bool $success
     * @param string|null $refundTransactionId
     * @param string|null $errorMessage
     * @return Webkul_RmaSystem_Model_Refund_PayPalRefundTransactionResult
     */
    public function load($success, $refundTransactionId, $errorMessage = null)
    {
        $this->success = $success;
        $this->refundTransactionId = $refundTransactionId;
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getRefundTransactionId()
    {
        return $this->refundTransactionId;
    }

    /**
     * @return null|string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}