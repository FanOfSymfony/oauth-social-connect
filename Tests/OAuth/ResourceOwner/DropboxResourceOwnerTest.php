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

use FOS\Bundle\OAuthSocialConnectBundle\OAuth\ResourceOwner\DropboxResourceOwner;

class DropboxResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = DropboxResourceOwner::class;
    protected $userResponse = '{"account_id": "1", "email": "bar"}';
    protected $paths = array(
        'identifier' => 'account_id',
        'nickname' => 'email',
        'realname' => 'email',
        'email' => 'email',
    );

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }
}
