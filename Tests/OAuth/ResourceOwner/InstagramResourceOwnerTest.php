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

use FOS\Bundle\OAuthSocialConnectBundle\OAuth\ResourceOwner\InstagramResourceOwner;
use FOS\Bundle\OAuthSocialConnectBundle\Tests\Fixtures\CustomUserResponse;

class InstagramResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = InstagramResourceOwner::class;
    protected $userResponse = <<<json
{
    "data": {
        "id":  "1",
        "username": "bar"
    }
}
json;
    protected $paths = array(
        'identifier' => 'data.id',
        'nickname' => 'data.username',
    );

    public function testCustomResponseClass()
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('user_response_class' => $class));

        /* @var $userResponse CustomUserResponse */
        $userResponse = $resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
    }
}
