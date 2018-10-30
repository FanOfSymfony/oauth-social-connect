<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\Bundle\OAuthBSocialConnectBundle;

/**
 * @author Marek Štípek
 */
final class FOSOAuthSocialConnectorEvents
{
    /**
     * @Event("FOS\Bundle\OAuthBSocialConnectBundle\Event\GetResponseUserEvent")
     */
    const REGISTRATION_INITIALIZE = 'hwi_oauth.registration.initialize';

    /**
     * @Event("FOS\Bundle\OAuthBSocialConnectBundle\Event\FormEvent")
     */
    const REGISTRATION_SUCCESS = 'hwi_oauth.registration.success';

    /**
     * @Event("FOS\Bundle\OAuthBSocialConnectBundle\Event\GetResponseUserEvent")
     */
    const REGISTRATION_COMPLETED = 'hwi_oauth.registration.completed';

    /**
     * @Event("FOS\Bundle\OAuthBSocialConnectBundle\Event\GetResponseUserEvent")
     */
    const CONNECT_INITIALIZE = 'hwi_oauth.connect.initialize';

    /**
     * @Event("FOS\Bundle\OAuthBSocialConnectBundle\Event\GetResponseUserEvent")
     */
    const CONNECT_CONFIRMED = 'hwi_oauth.connect.confirmed';

    /**
     * @Event("FOS\Bundle\OAuthBSocialConnectBundle\Event\FilterUserResponseEvent")
     */
    const CONNECT_COMPLETED = 'hwi_oauth.connect.completed';
}
