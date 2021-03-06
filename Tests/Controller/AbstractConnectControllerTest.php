<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\Bundle\OAuthSocialConnectBundle\Tests\Controller;

use FOS\Bundle\OAuthSocialConnectBundle\Connect\AccountConnectorInterface;
use FOS\Bundle\OAuthSocialConnectBundle\Controller\ConnectController;
use FOS\Bundle\OAuthSocialConnectBundle\OAuth\ResourceOwnerInterface;
use FOS\Bundle\OAuthSocialConnectBundle\Security\Core\Exception\AccountNotLinkedException;
use FOS\Bundle\OAuthSocialConnectBundle\Security\Http\ResourceOwnerMap;
use FOS\Bundle\OAuthSocialConnectBundle\Security\OAuthUtils;
use FOS\Bundle\OAuthSocialConnectBundle\Tests\Fixtures\CustomOAuthToken;
use FOS\Bundle\OAuthSocialConnectBundle\Tests\Fixtures\CustomUserResponse;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

abstract class AbstractConnectControllerTest extends TestCase
{
    /**
     * @var ConnectController
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EngineInterface
     */
    protected $twig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RouterInterface
     */
    protected $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResourceOwnerMap
     */
    protected $resourceOwnerMap;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResourceOwnerInterface
     */
    protected $resourceOwner;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AccountConnectorInterface
     */
    protected $accountConnector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OAuthUtils
     */
    protected $oAuthUtils;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UserCheckerInterface
     */
    protected $userChecker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    protected $session;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp()
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->setParameter('fos_oauth_social_connect.connect', true);
        $this->container->setParameter('fos_oauth_social_connect.firewall_names', array('default'));
        $this->container->setParameter('fos_oauth_social_connect.connect.confirmation', true);
        $this->container->setParameter('fos_oauth_social_connect.grant_rule', 'IS_AUTHENTICATED_REMEMBERED');

        $this->authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('security.authorization_checker', $this->authorizationChecker);

        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('security.token_storage', $this->tokenStorage);

        $this->twig = $this->getMockBuilder(EngineInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('twig', $this->twig);

        $this->router = $this->getMockBuilder(RouterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('router', $this->router);

        $this->resourceOwner = $this->getMockBuilder(ResourceOwnerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceOwner->expects($this->any())
            ->method('getUserInformation')
            ->willReturn(new CustomUserResponse())
        ;
        $this->resourceOwnerMap = $this->getMockBuilder(ResourceOwnerMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceOwnerMap->expects($this->any())
            ->method('getResourceOwnerByName')
            ->withAnyParameters()
            ->willReturn($this->resourceOwner);
        $this->container->set('fos_oauth_social_connect.resource_ownermap.default', $this->resourceOwnerMap);

        $this->accountConnector = $this->getMockBuilder(AccountConnectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('fos_oauth_social_connect.account.connector', $this->accountConnector);

        $this->oAuthUtils = $this->getMockBuilder(OAuthUtils::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('fos_oauth_social_connect.security.oauth_utils', $this->oAuthUtils);

        $this->userChecker = $this->getMockBuilder(UserCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('fos_oauth_social_connect.user_checker', $this->userChecker);

        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('event_dispatcher', $this->eventDispatcher);

        $this->formFactory = $this->getMockBuilder(FormFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('form.factory', $this->formFactory);

        $this->session = $this->getMockBuilder(SessionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = Request::create('/');
        $this->request->setSession($this->session);

        $this->controller = new ConnectController();
        $this->controller->setContainer($this->container);
    }

    /**
     * @return AccountNotLinkedException
     */
    protected function createAccountNotLinkedException()
    {
        $accountNotLinked = new AccountNotLinkedException();
        $accountNotLinked->setResourceOwnerName('facebook');
        $accountNotLinked->setToken(new CustomOAuthToken());

        return $accountNotLinked;
    }

    /**
     * @return string
     */
    protected function getAuthenticationErrorKey()
    {
        return Security::AUTHENTICATION_ERROR;
    }

    protected function mockAuthorizationCheck($granted = true)
    {
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn($granted)
        ;
    }
}
