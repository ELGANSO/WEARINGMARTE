<?php

/**
 * Extended session model.
 */
class TemplateMonster_SocialLogin_Model_Session extends Mage_Customer_Model_Session
{
    /**
     * Login by OAuth.
     *
     * @param array $data
     *
     * @return bool
     */
    public function loginByOAuth(array $data)
    {
        /** @var $customer TemplateMonster_SocialLogin_Model_Customer */
        $customer = Mage::getModel('sociallogin/customer');
        $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

        if ($customer->authenticateByOAuth($data)) {
            $this->setCustomerAsLoggedIn($customer);
        } else {
            $customer->setData($data)->setIsNew(true)->save();
            $this->setCustomerAsLoggedIn($customer);
        }

        $customer->bindToServiceProvider($data['provider_code'], $data['provider_id']);

        return true;
    }
}
