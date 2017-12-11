<?php

/**
 * Social Login block.
 */
class TemplateMonster_SocialLogin_Block_Login extends Mage_Core_Block_Template
{
    /**
     * Check if has providers.
     *
     * @return bool
     */
    public function hasProviders()
    {
        return count($this->getProviders()) > 0;
    }

    /**
     * Get providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->getProviderMap()->getAll();
    }

    /**
     * Get grant url.
     *
     * @param TemplateMonster_SocialLogin_Model_Provider_Interface $provider
     *
     * @return string
     */
    public function getGrantUrl(TemplateMonster_SocialLogin_Model_Provider_Interface $provider)
    {
        return Mage::getUrl('sociallogin/index/grant', array(
            'provider' => $provider->getCode(),
            'form_key' => Mage::getSingleton('core/session')->getFormKey(),
            '_secure' => true,
        ));
    }

    /**
     * Get provider image.
     *
     * @param TemplateMonster_SocialLogin_Model_Provider_Interface $provider
     *
     * @return string
     */
    public function getProviderImage(TemplateMonster_SocialLogin_Model_Provider_Interface $provider)
    {
        return $this->getSkinUrl(sprintf('images/sociallogin/providers/%s.png', $provider->getCode()));
    }

    /**
     * Get provider map.
     *
     * @return TemplateMonster_SocialLogin_Model_Provider_Map
     */
    protected function getProviderMap()
    {
        return Mage::getSingleton('sociallogin/provider_map');
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!Mage::helper('sociallogin')->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
