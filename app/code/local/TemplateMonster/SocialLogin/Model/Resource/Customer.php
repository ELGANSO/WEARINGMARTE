<?php

/**
 * Extended customer resource model.
 */
class TemplateMonster_SocialLogin_Model_Resource_Customer extends Mage_Customer_Model_Resource_Customer
{
    /**
     * Load by email or OAuth provider.
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param string                       $email
     * @param string                       $providerCode
     * @param string                       $providerId
     *
     * @return $this
     *
     * @throws Mage_Core_Exception
     */
    public function loadByEmailOrProvider(Mage_Customer_Model_Customer $customer, $email, $providerCode, $providerId)
    {
        $adapter = $this->_getReadAdapter();
        $bind = array(
            'email' => $email,
            'provider_code' => $providerCode,
            'provider_id' => $providerId,
        );
        $select = $adapter->select()
            ->from($this->getEntityTable(), array($this->getEntityIdField()))
            ->joinLeft(
                array('op' => $this->getTable('sociallogin/provider')),
                'op.customer_id = entity_id',
                array('op.id')
            )
            ->where('email = :email')
            ->orWhere('op.provider_code = :provider_code AND op.provider_id = :provider_id')
        ;

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            if (!$customer->hasData('website_id')) {
                Mage::throwException(
                    Mage::helper('customer')->__('Customer website ID must be specified when using the website scope')
                );
            }
            $bind['website_id'] = (int) $customer->getWebsiteId();
            $select->where('website_id = :website_id');
        }

        $customerId = $adapter->fetchOne($select, $bind);
        if ($customerId) {
            $this->load($customer, $customerId);
        } else {
            $customer->setData(array());
        }

        return $this;
    }

    /**
     * Bind customer to OAuth service provider.
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param string                       $providerCode
     * @param string                       $providerId
     *
     * @return $this
     */
    public function bindToServiceProvider(Mage_Customer_Model_Customer $customer, $providerCode, $providerId)
    {
        $adapter = $this->_getWriteAdapter();
        $bind = array(
            'customer_id' => $customer->getId(),
            'provider_code' => $providerCode,
            'provider_id' => $providerId,
            'created_at' => new Zend_Db_Expr('NOW()'),
        );
        $adapter->insertIgnore($this->getTable('sociallogin/provider'), $bind);

        return $this;
    }
}
