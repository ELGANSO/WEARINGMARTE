<?php

require_once Mage::getModuleDir('controllers', 'Mage_Customer').DS.'AccountController.php';

/**
 * Extended customer login controller.
 */
class TemplateMonster_SocialLogin_IndexController extends Mage_Customer_AccountController
{
    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        if (!$this->_getHelper('sociallogin')->isEnabled()) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }

        if (!in_array($this->getRequest()->getActionName(), array('grant', 'connect'))) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }

        // bypassing parents constructor and calling ancestors constructor directly
        return Mage_Core_Controller_Front_Action::preDispatch();
    }

    /**
     * Grant action.
     */
    public function grantAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirectUrl($this->_getHelper('customer')->getLoginUrl());

            return;
        }

        $alias = $this->getRequest()->getParam('provider');

        try {
            $provider = $this->_getProviders()->getByAlias($alias);
        } catch (TemplateMonster_SocialLogin_Exception $e) {
            $this->_getSession()->addError('Invalid or missing OAuth service provider specified.');
            $this->_redirectUrl($this->_getHelper('customer')->getLoginUrl());

            return;
        }

        $this->getResponse()->setRedirect($provider->getAuthorizationUrl());
    }

    /**
     * Connect action.
     */
    public function connectAction()
    {
        $alias = $this->getRequest()->getParam('provider');

        try {
            $provider = $this->_getProviders()->getByAlias($alias);

            $token = $provider->getAccessToken();
            $data = $provider->getUserData($token);

            $this->_getSession()->loginByOauth($data);
        } catch (TemplateMonster_SocialLogin_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectUrl($this->_getHelper('customer')->getLoginUrl());

            return;
        } catch (Exception $e) {
            $this->_getSession()->addError('Unknown error occurred while trying to login.');
            $this->_redirectUrl($this->_getHelper('customer')->getLoginUrl());

            return;
        }

        $customer = $this->_getSession()->getCustomer();
        if ($customer->getIsNew()) {
            $this->_dispatchRegisterSuccess($customer);
            $this->_welcomeCustomer($customer, true);
        }

        $this->_redirectSuccess($this->_getHelper('customer')->getAccountUrl());
    }

    /**
     * Get providers map.
     *
     * @return TemplateMonster_SocialLogin_Model_Provider_Map
     */
    protected function _getProviders()
    {
        return Mage::getSingleton('sociallogin/provider_map');
    }

    /**
     * Get session instance.
     *
     * @return TemplateMonster_SocialLogin_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('sociallogin/session');
    }
}
