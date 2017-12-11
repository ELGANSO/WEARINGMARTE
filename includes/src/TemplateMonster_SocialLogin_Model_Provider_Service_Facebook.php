<?php

/**
 * Facebook OAuth service provider implementation.
 */
class TemplateMonster_SocialLogin_Model_Provider_Service_Facebook extends TemplateMonster_SocialLogin_Model_Provider_Abstract
{
    /**
     * {@inheritdoc}
     */
    protected $authorizationUrl = 'https://www.facebook.com/v2.0/dialog/oauth';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://graph.facebook.com/v2.0/oauth/access_token';

    /**
     * {@inheritdoc}
     */
    protected $userDataUrl = 'https://graph.facebook.com/v2.0/me';

    /**
     * {@inheritdoc}
     */
    protected $normalizedFields = array(
        'id' => 'provider_id',
        'name' => 'firstname',
        'email' => 'email',
    );

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'facebook';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Facebook';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationUrlParams()
    {
        return array_merge(
            parent::getAuthorizationUrlParams(),
            array(
                'scope' => 'email',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserDataUrlParams()
    {
        return array_merge(
            parent::getUserDataUrlParams(),
            array(
                'fields' => 'id,name,email',
            )
        );
    }
}
