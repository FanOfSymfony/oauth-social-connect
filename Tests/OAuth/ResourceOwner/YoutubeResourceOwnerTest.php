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

use FOS\Bundle\OAuthSocialConnectBundle\OAuth\ResourceOwner\YoutubeResourceOwner;
use Symfony\Component\Security\Http\HttpUtils;

class YoutubeResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = YoutubeResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "name": "bar"
}
json;

    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'name',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'picture',
    );

    protected $expectedUrls = array(
        'authorization_url' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=read&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube.readonly&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline',
    );

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function testInvalidAccessTypeOptionValueThrowsException()
    {
        $this->createResourceOwner($this->resourceOwnerName, array('access_type' => 'invalid'));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function testInvalidApprovalPromptOptionValueThrowsException()
    {
        $this->createResourceOwner($this->resourceOwnerName, array('approval_prompt' => 'invalid'));
    }

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube.readonly&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testRequestVisibleActions()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('request_visible_actions' => 'http://schemas.google.com/AddActivity'));

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube.readonly&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&request_visible_actions=http%3A%2F%2Fschemas.google.com%2FAddActivity',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testApprovalPromptForce()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('approval_prompt' => 'force'));

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube.readonly&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&approval_prompt=force',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testHdParameter()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('hd' => 'mycollege.edu'));
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube.readonly&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&hd=mycollege.edu',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testRevokeToken()
    {
        $this->httpResponseHttpCode = 200;
        $this->mockHttpClient('{"access_token": "bar"}', 'application/json');

        $this->assertTrue($this->resourceOwner->revokeToken('token'));
    }

    public function testRevokeTokenFails()
    {
        $this->httpResponseHttpCode = 401;
        $this->mockHttpClient('{"access_token": "bar"}', 'application/json');

        $this->assertFalse($this->resourceOwner->revokeToken('token'));
    }

    protected function setUpResourceOwner($name, HttpUtils $httpUtils, array $options)
    {
        return parent::setUpResourceOwner(
            $name,
            $httpUtils,
            array_merge(
                array(
                    'access_type' => 'offline',
                ),
                $options
            )
        );
    }
}
