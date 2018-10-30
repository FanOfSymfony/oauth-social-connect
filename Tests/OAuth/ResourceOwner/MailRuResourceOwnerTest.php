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

use FOS\Bundle\OAuthSocialConnectBundle\OAuth\ResourceOwner\MailRuResourceOwner;

class MailRuResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = MailRuResourceOwner::class;
    protected $userResponse = <<<json
[
    {
        "user_id": "1",
        "name": "bar",
        "email": "baz"
    }
]
json;

    protected $paths = array(
        'identifier' => 'user_id',
        'nickname' => 'name',
        'email' => 'email',
    );
}
