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

use Http\Client\Exception\TransferException;
use FOS\Bundle\OAuthSocialConnectBundle\OAuth\Exception\HttpTransportException;
use FOS\Bundle\OAuthSocialConnectBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use FOS\Bundle\OAuthSocialConnectBundle\Tests\Fixtures\CustomUserResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class GenericOAuth2ResourceOwnerTest extends ResourceOwnerTestCase
{
    protected $resourceOwnerClass = GenericOAuth2ResourceOwner::class;
    /**
     * @var GenericOAuth2ResourceOwner
     */
    protected $resourceOwner;
    protected $resourceOwnerName;

    protected $tokenData = array('access_token' => 'token');

    protected $options = array(
        'client_id' => 'clientid',
        'client_secret' => 'clientsecret',

        'infos_url' => 'http://user.info/?test=1',
        'authorization_url' => 'http://user.auth/?test=2',
        'access_token_url' => 'http://user.access/?test=3',

        'attr_name' => 'access_token',
    );

    protected $userResponse = <<<json
{
    "id":  "1",
    "foo": "bar"
}
json;

    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'foo',
        'realname' => 'foo_disp',
    );

    protected $expectedUrls = array(
        'authorization_url' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&redirect_uri=http%3A%2F%2Fredirect.to%2F',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
    );

    public function setUp()
    {
        $this->resourceOwnerName = str_replace(array('generic', 'resourceownertest'), '', strtolower(__CLASS__));
        $this->resourceOwner = $this->createResourceOwner($this->resourceOwnerName);
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function testUndefinedOptionThrowsException()
    {
        $this->createResourceOwner($this->resourceOwnerName, array('non_existing' => null));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function testInvalidOptionValueThrowsException()
    {
        $this->createResourceOwner($this->resourceOwnerName, array('csrf' => 'invalid'));
    }

    public function testHandleRequest()
    {
        $request = new Request(array('test' => 'test'));

        $this->assertFalse($this->resourceOwner->handles($request));

        $request = new Request(array('code' => 'test'));

        $this->assertTrue($this->resourceOwner->handles($request));

        $request = new Request(array('code' => 'test', 'test' => 'test'));

        $this->assertTrue($this->resourceOwner->handles($request));
    }

    public function testGetUserInformation()
    {
        $this->mockHttpClient($this->userResponse, 'application/json; charset=utf-8');

        /** @var $userResponse \FOS\Bundle\OAuthSocialConnectBundle\OAuth\Response\AbstractUserResponse */
        $userResponse = $this->resourceOwner->getUserInformation($this->tokenData);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformationFailure()
    {
        $exception = new TransferException();

        $this->httpClient->expects($this->once())
            ->method('send')
            ->will($this->throwException($exception));

        try {
            $this->resourceOwner->getUserInformation($this->tokenData);
            $this->fail('An exception should have been raised');
        } catch (HttpTransportException $e) {
            $this->assertSame($exception, $e->getPrevious());
        }
    }

    public function testGetAuthorizationUrl()
    {
        if (!$this->csrf) {
            $this->state = null;
        }

        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName);

        if (!$this->csrf) {
            $this->storage->expects($this->never())
                ->method('save');
        } else {
            $this->storage->expects($this->once())
                ->method('save')
                ->with($resourceOwner, $this->state, 'csrf_state');
        }

        $this->assertEquals(
            $this->expectedUrls['authorization_url'],
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );

        $this->state = 'random';
    }

    public function testGetAuthorizationUrlWithEnabledCsrf()
    {
        if ($this->csrf) {
            $this->markTestSkipped('CSRF is enabled for this Resource Owner.');
        }

        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('csrf' => true));

        $this->storage->expects($this->once())
            ->method('save')
            ->with($resourceOwner, $this->state, 'csrf_state');

        $this->assertEquals(
            $this->expectedUrls['authorization_url_csrf'],
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetAccessToken()
    {
        $this->mockHttpClient('access_token=code');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            array('access_token' => 'code'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenJsonResponse()
    {
        $this->mockHttpClient('{"access_token": "code"}', 'application/json');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            array('access_token' => 'code'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenJsonCharsetResponse()
    {
        $this->mockHttpClient('{"access_token": "code"}', 'application/json; charset=utf-8');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            array('access_token' => 'code'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenTextJavascriptResponse()
    {
        $this->mockHttpClient('{"access_token": "code"}', 'text/javascript');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            array('access_token' => 'code'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenTextJavascriptCharsetResponse()
    {
        $this->mockHttpClient('{"access_token": "code"}', 'text/javascript; charset=utf-8');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            array('access_token' => 'code'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenFailedResponse()
    {
        $this->mockHttpClient('invalid');
        $request = new Request(array('code' => 'code'));

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenErrorResponse()
    {
        $this->mockHttpClient('error=foo');
        $request = new Request(array('code' => 'code'));

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testRefreshAccessToken()
    {
        $this->mockHttpClient('{"access_token": "bar", "expires_in": 3600}', 'application/json');
        $accessToken = $this->resourceOwner->refreshAccessToken('foo');

        $this->assertEquals('bar', $accessToken['access_token']);
        $this->assertEquals(3600, $accessToken['expires_in']);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testRefreshAccessTokenInvalid()
    {
        $this->mockHttpClient('invalid');

        $this->resourceOwner->refreshAccessToken('foo');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testRefreshAccessTokenError()
    {
        $this->mockHttpClient('{"error": "invalid"}', 'application/json');

        $this->resourceOwner->refreshAccessToken('foo');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testRevokeToken()
    {
        $this->resourceOwner->revokeToken('token');
    }

    public function testGetSetName()
    {
        $this->assertEquals($this->resourceOwnerName, $this->resourceOwner->getName());
        $this->resourceOwner->setName('foo');
        $this->assertEquals('foo', $this->resourceOwner->getName());
    }

    public function testCsrfTokenIsValidWhenDisabled()
    {
        if ($this->csrf) {
            $this->markTestSkipped('CSRF is enabled for this Resource Owner.');
        }

        $this->storage->expects($this->never())
            ->method('fetch');

        $this->assertTrue($this->resourceOwner->isCsrfTokenValid('whatever you want'));
    }

    public function testCsrfTokenValid()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('csrf' => true));

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($resourceOwner, 'valid_token', 'csrf_state')
            ->will($this->returnValue('valid_token'));

        $this->assertTrue($resourceOwner->isCsrfTokenValid('valid_token'));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testCsrfTokenInvalid()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('csrf' => true));

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($resourceOwner, 'invalid_token', 'csrf_state')
            ->will($this->throwException(new InvalidOptionsException('No data available in storage.')));

        $resourceOwner->isCsrfTokenValid('invalid_token');
    }

    public function testCustomResponseClass()
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('user_response_class' => $class));

        $this->mockHttpClient();

        /** @var CustomUserResponse */
        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    protected function createResourceOwner($name, array $options = array(), array $paths = array())
    {
        $resourceOwner = parent::createResourceOwner($name, $options, $paths);

        $reflection = new \ReflectionClass(get_class($resourceOwner));
        $stateProperty = $reflection->getProperty('state');
        $stateProperty->setAccessible(true);
        $stateProperty->setValue($resourceOwner, $this->state);

        return $resourceOwner;
    }
}
