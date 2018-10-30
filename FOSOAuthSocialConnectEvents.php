<?php

namespace FOS\Bundle\OAuthSocialConnectBundle;

/**
 * @author Marek Štípek
 */
final class FOSOAuthSocialConnectEvents
{
    /**
     * @Event("FOS\Bundle\OAuthSocialConnectBundle\Event\GetResponseUserEvent")
     */
    const REGISTRATION_INITIALIZE = 'fos_oauth_social_connect.registration.initialize';

    /**
     * @Event("FOS\Bundle\OAuthSocialConnectBundle\Event\FormEvent")
     */
    const REGISTRATION_SUCCESS = 'fos_oauth_social_connect.registration.success';

    /**
     * @Event("FOS\Bundle\OAuthSocialConnectBundle\Event\GetResponseUserEvent")
     */
    const REGISTRATION_COMPLETED = 'fos_oauth_social_connect.registration.completed';

    /**
     * @Event("FOS\Bundle\OAuthSocialConnectBundle\Event\GetResponseUserEvent")
     */
    const CONNECT_INITIALIZE = 'fos_oauth_social_connect.connect.initialize';

    /**
     * @Event("FOS\Bundle\OAuthSocialConnectBundle\Event\GetResponseUserEvent")
     */
    const CONNECT_CONFIRMED = 'fos_oauth_social_connect.connect.confirmed';

    /**
     * @Event("FOS\Bundle\OAuthSocialConnectBundle\Event\FilterUserResponseEvent")
     */
    const CONNECT_COMPLETED = 'fos_oauth_social_connect.connect.completed';
}
