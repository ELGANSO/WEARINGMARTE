<?php

/**
 * Abstract class for OAuth providers.
 */
abstract class TemplateMonster_SocialLogin_Model_Provider_Abstract implements TemplateMonster_SocialLogin_Model_Provider_Interface
{
    /**
     * OAuth authorization url.
     *
     * @var string
     */
    protected $authorizationUrl;

    /**
     * OAuth access token url.
     *
     * @var string
     */
    protected $accessTokenUrl;

    /**
     * OAuth user data url.
     *
     * @var string
     */
    protected $userDataUrl;

    /**
     * Normalized urls.
     *
     * @var array
     */
    protected $normalizedFields = array();

    /**
     * Provider options.
     *
     * @var array
     */
    protected $options = array();

    /**
     * Session state key.
     */
    const SESSION_STATE_KEY = '%s-state';

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->resolveOptions($options);
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return (int) $this->options['sort_order'];
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return (bool) $this->options['enabled'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl()
    {
        return sprintf('%s?%s',
            $this->authorizationUrl,
            http_build_query($this->getAuthorizationUrlParams())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        $url = $this->getAccessTokenUrl();
        $response = $this->getResponse($url, 'post');
        $token = $this->parseResponse($response);

        $this->checkAccessToken($token);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserData($token)
    {
        $url = $this->getUserDataUrl($token);
        $response = $this->getResponse($url);
        $data = $this->parseResponse($response);
        $normalized = $this->normalizeData($data);

        $this->checkUserData($normalized);

        return $normalized;
    }

    /**
     * Build params query string for authorization url.
     *
     * @return array
     */
    protected function getAuthorizationUrlParams()
    {
        return array(
            'response_type' => 'code',
            'client_id' => $this->options['client_id'],
            'redirect_uri' => $this->getRedirectUri(),
            'state' => $this->generateAndRememberState(),
        );
    }

    /**
     * Get access token url.
     *
     * @return string
     */
    protected function getAccessTokenUrl()
    {
        return sprintf(
            '%s?%s', $this->accessTokenUrl,
            http_build_query($this->getAccessTokenUrlParams())
        );
    }

    /**
     * Get access url url params.
     *
     * @return array
     */
    protected function getAccessTokenUrlParams()
    {
        return array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
            'code' => $this->getAuthorizationCode(),
            'redirect_uri' => $this->getRedirectUri(),
        );
    }

    /**
     * Check access token.
     *
     * @param array $token
     *
     * @throws TemplateMonster_SocialLogin_Exception
     */
    protected function checkAccessToken(array $token)
    {
        if (empty($token['access_token'])) {
            throw new TemplateMonster_SocialLogin_Exception(
                'Invalid or missing oauth token.',
                TemplateMonster_SocialLogin_Exception::TYPE_MISSING_TOKEN
            );
        }
    }

    /**
     * Get user data.
     *
     * @param array $token
     *
     * @return string
     */
    protected function getUserDataUrl(array $token)
    {
        return sprintf(
            '%s?%s&access_token=%s',
            $this->userDataUrl,
            http_build_query($this->getUserDataUrlParams()),
            $token['access_token']
        );
    }

    /**
     * Get user data url params.
     *
     * @return array
     */
    protected function getUserDataUrlParams()
    {
        return array(
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
        );
    }

    /**
     * Get request instance.
     *
     * @return Mage_Core_Controller_Request_Http
     */
    protected function getRequest()
    {
        return Mage::app()->getRequest();
    }

    /**
     * Get session instance.
     *
     * @return Mage_Core_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('core/session');
    }

    /**
     * Get response by url.
     *
     * @param string $url
     * @param string $method
     *
     * @return string
     */
    protected function getResponse($url, $method = 'get')
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ('post' == $method) {
            $parts = parse_url($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parts['query']);
        }

        return curl_exec($ch);
    }

    /**
     * Parse response.
     *
     * @param string $content
     *
     * @return array|mixed
     */
    protected function parseResponse($content)
    {
        if (empty($content)) {
            return array();
        }

        // assume response in JSON, fallback to key=value pairs otherwise
        $response = json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            parse_str($content, $response);
        }

        return $response;
    }

    /**
     * Resolve options.
     *
     * @param array $options
     */
    protected function resolveOptions(array $options)
    {
        if (isset($options['enabled']) && $options['enabled']) {
            assert(!empty($options['client_id']), sprintf('Missing configuration option %s client id.', $this->getName()));
            assert(!empty($options['client_secret']), sprintf('Missing configuration option Facebook client secret.', $this->getName()));
        }
    }

    /**
     * Normalize data.
     *
     * @param array $data
     *
     * @return array
     */
    protected function normalizeData(array $data)
    {
        $normalized = array();
        foreach ($this->normalizedFields as $before => $after) {
            $normalized[$after] = $data[$before];
        }
        $normalized['provider_code'] = $this->getCode();

        return $normalized;
    }

    /**
     * Check user data.
     *
     * @param array $data
     *
     * @throws TemplateMonster_SocialLogin_Exception
     */
    protected function checkUserData(array $data)
    {
        foreach (array('email', 'provider_id') as $field) {
            if (empty($data[$field])) {
                throw new TemplateMonster_SocialLogin_Exception(
                    sprintf(
                        'Field «%s» is missing on your %s account.',
                        $field,
                        $this->getName()
                    ),
                    TemplateMonster_SocialLogin_Exception::TYPE_INVALID_USER_DATA
                );
            }
        }
    }

    /**
     * Get redirect uri.
     *
     * @return string
     */
    protected function getRedirectUri()
    {
        return Mage::helper('sociallogin')->getRedirectUri($this);
    }

    /**
     * Generate and remember in session un guessable state.
     *
     * @return string
     */
    protected function generateAndRememberState()
    {
        $state = $this->generateState();
        $this->getSession()->setData(
            sprintf(self::SESSION_STATE_KEY, $this->getCode()),
            $state
        );

        return $state;
    }

    /**
     * Generate state.
     *
     * @return string
     */
    protected function generateState()
    {
        return md5(microtime(true).uniqid('', true));
    }

    /**
     * Get authorization code.
     *
     * @return mixed
     *
     * @throws TemplateMonster_SocialLogin_Exception
     */
    protected function getAuthorizationCode()
    {
        $sessionState = $this->getSession()->getData(
            sprintf(self::SESSION_STATE_KEY, $this->getCode()),
            true
        );
        $requestState = $this->getRequest()->getParam('state');

        if ($sessionState !== $requestState) {
            throw new TemplateMonster_SocialLogin_Exception(
                'Authorization state mismatch.',
                TemplateMonster_SocialLogin_Exception::TYPE_AUTHORIZATION_STATE_MISMATCH
            );
        }

        return $this->getRequest()->getParam('code');
    }
}
