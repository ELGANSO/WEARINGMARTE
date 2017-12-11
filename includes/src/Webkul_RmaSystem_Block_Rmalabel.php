<?php

class Webkul_RmaSystem_Block_Rmalabel extends Mage_Core_Block_Template
{
    /** @var int */
    private $rmaId;

    /**
     * @return int
     */
    public function getRmaId()
    {
        return $this->rmaId;
    }

    /**
     * @param int $rmaId
     */
    public function setRmaId($rmaId)
    {
        $this->rmaId = $rmaId;
    }

    /**
     * @return string
     */
    public function getLabelUrl()
    {
        $customerId = Mage::helper('customer')->getCustomer()->getId();
        $labelName = Mage::helper('rmasystem')->getLabelName($customerId, $this->rmaId);
        return '/media/rmalabels/' . $labelName . '.pdf';
    }
}