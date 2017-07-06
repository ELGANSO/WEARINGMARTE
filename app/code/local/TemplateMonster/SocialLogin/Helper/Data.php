<?php

/**
 * Base module helper.
 */
class TemplateMonster_SocialLogin_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Xpath to enabled switcher.
     */
    const XML_PATH_ENABLED = 'sociallogin/general/enabled';

    /**
     * Check is module enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Get redirect uri
     *
     * @param TemplateMonster_SocialLogin_Model_Provider_Interface|string $provider
     *
     * @return string
     */
    public function getRedirectUri($provider)
    {
        if ($provider instanceof TemplateMonster_SocialLogin_Model_Provider_Interface) {
            $provider = $provider->getCode();
        }

        return $this->_getUrl('sociallogin/index/connect', array(
            'provider' => $provider,
            '_secure' => true,
        ));
    }
}
