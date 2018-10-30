<?php

namespace FOS\Bundle\OAuthSocialConnectBundle\Twig\Extension;

use FOS\Bundle\OAuthSocialConnectBundle\Templating\Helper\OAuthHelper;

/**
 * OAuthExtension.
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class OAuthExtension extends \Twig_Extension
{
    /**
     * @var OAuthHelper
     */
    protected $helper;

    /**
     * @param OAuthHelper $helper
     */
    public function __construct(OAuthHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('fos_oauth_social_connect_authorization_url', array($this, 'getAuthorizationUrl')),
            new \Twig_SimpleFunction('fos_oauth_social_connect_login_url', array($this, 'getLoginUrl')),
            new \Twig_SimpleFunction('fos_oauth_social_connect_resource_owners', array($this, 'getResourceOwners')),
        );
    }

    /**
     * @return array
     */
    public function getResourceOwners()
    {
        return $this->helper->getResourceOwners();
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getLoginUrl($name)
    {
        return $this->helper->getLoginUrl($name);
    }

    /**
     * @param string $name
     * @param string $redirectUrl     Optional
     * @param array  $extraParameters Optional
     *
     * @return string
     */
    public function getAuthorizationUrl($name, $redirectUrl = null, array $extraParameters = array())
    {
        return $this->helper->getAuthorizationUrl($name, $redirectUrl, $extraParameters);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'fos_oauth_social_connect';
    }
}
