<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\Bundle\OAuthSocialConnectBundle\Tests\Fixtures;

use FOS\Bundle\OAuthSocialConnectBundle\Security\Core\Authentication\Token\OAuthToken;

class CustomOAuthToken extends OAuthToken
{
    public function __construct()
    {
        parent::__construct(array(
            'access_token' => 'access_token_data',
        ), array(
            'ROLE_USER',
        ));

        $this->setUser(new User());
    }
}
