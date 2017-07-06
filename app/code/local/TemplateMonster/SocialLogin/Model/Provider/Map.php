<?php

/**
 * Providers map.
 */
class TemplateMonster_SocialLogin_Model_Provider_Map
{
    /**
     * Xpath to providers declaration list.
     */
    const XML_PATH_PROVIDERS = 'default/sociallogin/providers';

    /**
     * Xpath to provider service options.
     */
    const XML_PATH_PROVIDER_SERVICE = 'default/sociallogin/%s_service';

    /**
     * Provider map (alias => instance).
     *
     * @var array
     */
    private $map = array();

    /**
     * Is providers have been loaded.
     *
     * @var bool
     */
    private $isLoaded = false;

    /**
     * Get instance by alias.
     *
     * @param $alias
     *
     * @return false|TemplateMonster_SocialLogin_Model_Provider_Interface
     *
     * @throws TemplateMonster_SocialLogin_Exception
     */
    public function getByAlias($alias)
    {
        $this->loadProviders();

        if (!isset($this->map[$alias])) {
            throw new TemplateMonster_SocialLogin_Exception(
                sprintf('Invalid provider alias «%s».', $alias),
                TemplateMonster_SocialLogin_Exception::TYPE_PROVIDER_NOT_FOUND
            );
        }

        return $this->map[$alias];
    }

    /**
     * Get all providers.
     *
     * @return array
     */
    public function getAll()
    {
        $this->loadProviders();

        return $this->map;
    }

    /**
     * Load providers from configuration file.
     */
    protected function loadProviders()
    {
        if ($this->isLoaded) {
            return;
        }

        $node = Mage::getConfig()->getNode(self::XML_PATH_PROVIDERS);

        if (false === $node) {
            return;
        }

        $providers = $node->asArray();

        $map = array();
        foreach ($providers as $alias => $class) {
            $instance = $this->instantiateProvider($alias, $class);
            if ($instance->isEnabled()) {
                $map[$alias] = $instance;
            }
        }

        // sort service providers by position
        uasort($map, array($this, 'sortComparator'));

        $this->map = $map;
        $this->isLoaded = true;
    }

    /**
     * Instantiate provider class.
     *
     * @param $alias
     * @param $class
     *
     * @return false|TemplateMonster_SocialLogin_Model_Provider_Interface
     */
    protected function instantiateProvider($alias, $class)
    {
        $options = $this->getProviderOptions($alias);
        $provider = Mage::getModel($class, $options);

        if (!($provider instanceof TemplateMonster_SocialLogin_Model_Provider_Interface)) {
            throw new RuntimeException('Provider should implement TemplateMonster_SocialLogin_Model_Provider_Interface.');
        }

        return $provider;
    }

    /**
     * Get provider configuration options.
     *
     * @param string $alias
     *
     * @return array|string
     */
    protected function getProviderOptions($alias)
    {
        $xpath = sprintf(self::XML_PATH_PROVIDER_SERVICE, $alias);
        $node = Mage::getConfig()->getNode($xpath);

        if (false === $node) {
            return array();
        }

        return $node->asArray();
    }

    /**
     * Provider sort comparator by sort order.
     *
     * @param TemplateMonster_SocialLogin_Model_Provider_Interface $first
     * @param TemplateMonster_SocialLogin_Model_Provider_Interface $second
     *
     * @return int
     */
    protected function sortComparator(
        TemplateMonster_SocialLogin_Model_Provider_Interface $first,
        TemplateMonster_SocialLogin_Model_Provider_Interface $second
    ) {
        if ($first->getSortOrder() == $second->getSortOrder()) {
            return 0;
        }

        return $first->getSortOrder() > $second->getSortOrder() ? +1 : -1;
    }
}
