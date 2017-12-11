<?php

/**
 * Google redirect uri form field
 */
class TemplateMonster_SocialLogin_Block_Adminhtml_System_Config_Form_Field_Google_Redirect
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setValue(
            $this->helper('sociallogin')->getRedirectUri('google')
        );

        return parent::_getElementHtml($element);
    }
}