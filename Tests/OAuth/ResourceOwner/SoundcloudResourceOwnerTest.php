<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\Bundle\OAuthSocialConnectBundle\Tests\OAuth\ResourceOwner;

use FOS\Bundle\OAuthSocialConnectBundle\OAuth\ResourceOwner\SoundcloudResourceOwner;

class SoundcloudResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = SoundcloudResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "username": "bar",
    "full_name": "baz"
}
json;

    protected $expectedUrls = array(
        'authorization_url' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=non-expiring&redirect_uri=http%3A%2F%2Fredirect.to%2F',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=non-expiring&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
    );

    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'username',
        'realname' => 'full_name',
    );
}
