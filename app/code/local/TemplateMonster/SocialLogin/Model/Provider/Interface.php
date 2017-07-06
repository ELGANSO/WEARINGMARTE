<?php

/**
 * Interface for all OAuth providers.
 */
interface TemplateMonster_SocialLogin_Model_Provider_Interface
{
    /**
     * Get alias.
     *
     * @return string
     */
    public function getCode();

    /**
     * Get name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get sort order.
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Check is enabled.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Get authorization url.
     *
     * @return string
     */
    public function getAuthorizationUrl();

    /**
     * Get access token.
     *
     * @return string
     */
    public function getAccessToken();

    /**
     * Get user data.
     *
     * @param array $token
     *
     * @return array
     */
    public function getUserData($token);
}
