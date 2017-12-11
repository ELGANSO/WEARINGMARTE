<?php

class Webkul_RmaSystem_Model_Rma extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init("rmasystem/rma");
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setData('status', $status);
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this['status'];
    }

    /**
     * @return int
     */
    public function getResolutionType()
    {
        return $this['resolution_type'];
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this['order_id'];
    }

    /**
     * @return string
     */
    public function getAdditionalInfo()
    {
        return $this['additional_info'];
    }

    /**
     * @return DateTime|null
     */
    public function getPickupDate()
    {
        return $this['pickup_date'] === null
            ? null
            : new DateTime($this['pickup_date']);
    }

    /**
     * @return string
     */
    public function getPickupAddress()
    {
        return $this['pickup_address'];
    }

    /**
     * @return string
     */
    public function getPickupNumber()
    {
        return $this['pickup_number'];
    }

    /**
     * @return string
     */
    public function getPickupCity()
    {
        return $this['pickup_city'];
    }

    /**
     * @return string
     */
    public function getPickupPhone()
    {
        return $this['pickup_phone'];
    }

    /**
     * @return string
     */
    public function getPickupPostcode()
    {
        return $this['pickup_postcode'];
    }

    /**
     * @return string
     */
    public function getPickupRegion()
    {
        return $this['pickup_region'];
    }

    /**
     * @return bool
     */
    public function needsToBePickedUp()
    {
        return $this->isWaitingShipment() && $this->getPickupDate() !== null;
    }

    /**
     * @return Mage_Core_Model_Abstract|Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::getModel('sales/order')->load($this->getOrderId());
    }

    /**
     * @return Webkul_RmaSystem_Model_Mysql4_Items_Collection|Webkul_RmaSystem_Model_Items[]
     */
    public function getItems()
    {
        /** @var Webkul_RmaSystem_Model_Mysql4_Items_Collection $collection */
        $collection = Mage::getModel('rmasystem/items')->getCollection();
        $collection->addFieldToFilter('rma_id', $this->getId());
        return $collection;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this['created_at'];
    }

    /**
     * @param bool $container
     * @return string
     */
    public function getStatusText($container = false)
    {
        $helper = Mage::helper('rmasystem');

        $text = '';
        $class = '';

        switch($this->getStatus())
        {
            case Webkul_RmaSystem_Model_Constants::StatusPending:
                $text = $helper->__('Pending');
                $class = 'wk_rma_status_pending';
                break;
            case Webkul_RmaSystem_Model_Constants::StatusWaitingShipment:
                $text = $helper->__('Waiting shipment');
                $class = 'wk_rma_status_processing';
                break;
            case Webkul_RmaSystem_Model_Constants::StatusAccepted:
                $text = $helper->__('Accepted');
                $class = 'wk_rma_status_solve';
                break;
            case Webkul_RmaSystem_Model_Constants::StatusDenied:
                $text = $helper->__('Denied');
                $class = 'wk_rma_status_decline';
                break;
            case Webkul_RmaSystem_Model_Constants::StatusCancelled:
                $text = $helper->__('Cancelled');
                $class = 'wk_rma_status_cancel';
                break;
        }

        return $container
            ? sprintf('<span class="%s">%s</span>', $class, $text)
            : $text;

    }

    /**
     * @return array
     */
    public function getStatusOptions()
    {
        $helper = Mage::helper('rmasystem');

        return [
            Webkul_RmaSystem_Model_Constants::StatusPending => $helper->__('Pending'),
            Webkul_RmaSystem_Model_Constants::StatusWaitingShipment => $helper->__('Waiting shipment'),
            Webkul_RmaSystem_Model_Constants::StatusAccepted => $helper->__('Accepted'),
            Webkul_RmaSystem_Model_Constants::StatusDenied => $helper->__('Denied'),
            Webkul_RmaSystem_Model_Constants::StatusCancelled => $helper->__('Cancelled')
        ];
    }

    /**
     * @return string
     */
    public function getResolutionTypeText()
    {
        $helper = Mage::helper('rmasystem');

        switch($this->getResolutionType())
        {
            case Webkul_RmaSystem_Model_Constants::ResolutionTypeRefund:
                return $helper->__('Refund');
            case Webkul_RmaSystem_Model_Constants::ResolutionTypeExchange:
                return $helper->__('Size exchange');
            default:
                return '';
        }
    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        return in_array(
            $this->getStatus(),
            [
                Webkul_RmaSystem_Model_Constants::StatusPending,
                Webkul_RmaSystem_Model_Constants::StatusWaitingShipment
            ]
        );
    }

    /**
     * @return bool
     */
    public function isWaitingShipment()
    {
        return $this->getStatus() == Webkul_RmaSystem_Model_Constants::StatusWaitingShipment;
    }

    /**
     * @return bool
     */
    public function canBeAccepted()
    {
        // Si se trata de una devolución no hay problema...
        if($this->isRefund())
        {
            return true;
        }

        // ...pero en caso de que sea un cambio de talla, tenemos que comprobar
        // que todas las tallas solicitadas estén disponibles todavía
        foreach($this->getItems() as $item)
        {
            $product = $item->getRequestedProduct();
            /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
            $stockItem = $product['stock_item'];
            if(!$stockItem->getIsInStock())
            {
                return false;
            }
        }

        return true;
    }

    /**
     * @return $this
     */
    public function accept()
    {
        $this->setStatus(Webkul_RmaSystem_Model_Constants::StatusAccepted);
        return $this;
    }

    /**
     * @return $this
     */
    public function deny()
    {
        $this->setStatus(Webkul_RmaSystem_Model_Constants::StatusDenied);
        return $this;
    }

    /**
     * @return bool
     */
    public function isRefund()
    {
        return $this->getResolutionType() == Webkul_RmaSystem_Model_Constants::ResolutionTypeRefund;
    }

    /**
     * @return bool
     */
    public function isSizeExchange()
    {
        return $this->getResolutionType() == Webkul_RmaSystem_Model_Constants::ResolutionTypeExchange;
    }

    /**
     * @return bool
     */
    public function hasAdditionalInfo()
    {
        return strlen($this->getAdditionalInfo()) > 0;
    }

    /**
     * @return string
     */
    public function getLabelUrl()
    {
        $customerId = Mage::helper('customer')->getCustomer()->getId();
        return Mage::helper('rmasystem')->getLabelUrl($customerId, $this->getId());
    }
}