<?php

class Webkul_RmaSystem_Model_Conversation extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('rmasystem/conversation');
    }

    public function setRmaId($id)
    {
        $this->setData('rma_id', $id);
        return $this;
    }

    public function setMessage($message)
    {
        $this->setData('message', $message);
        return $this;
    }

    public function setCreatedAt($createdAt)
    {
        $this->setData('created_at', $createdAt);
        return $this;
    }

    public function setSender($sender)
    {
        $this->setData('sender', $sender);
        return $this;
    }
}