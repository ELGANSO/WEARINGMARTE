<?php

/**
 * Extended customer model.
 */
class TemplateMonster_SocialLogin_Model_Customer extends Mage_Customer_Model_Customer
{
    /**
     * Constructor.
     */
    public function _construct()
    {
        $this->_init('sociallogin/customer');
    }

    /**
     * Authenticate user by OAuth.
     *
     * @param array $data
     *
     * @return bool
     */
    public function authenticateByOAuth(array $data)
    {
        $this->loadByEmailOrProvider($data['email'], $data['provider_code'], $data['provider_id']);

        if (!$this->getId()) {
            return false;
        }

        Mage::dispatchEvent('customer_customer_authenticated', array(
            'model' => $this,
            'is_using_oauth' => true,
            'data' => $data,
        ));

        return true;
    }

    /**
     * Load user by provider id.
     *
     * @param string $email
     * @param string $providerCode
     * @param string $providerId
     *
     * @return $this
     */
    public function loadByEmailOrProvider($email, $providerCode, $providerId)
    {
        $this->_getResource()->loadByEmailOrProvider($this, $email, $providerCode, $providerId);

        return $this;
    }

    /**
     * Bind to OAuth service provider.
     *
     * @param string $providerCode
     * @param string $providerId
     *
     * @return $this
     */
    public function bindToServiceProvider($providerCode, $providerId)
    {
        $this->_getResource()->bindToServiceProvider($this, $providerCode, $providerId);

        return $this;
    }
}
