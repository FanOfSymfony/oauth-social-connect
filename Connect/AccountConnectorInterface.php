<?php

namespace FOS\Bundle\OAuthBSocialConnectBundle\Connect;

use FOS\Bundle\OAuthBSocialConnectBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Account connector objects are responsible for connecting an OAuth response
 * to the appropriate fields of the user object.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
interface AccountConnectorInterface
{
    /**
     * Connects the response to the user object.
     *
     * @param UserInterface         $user     The user object
     * @param UserResponseInterface $response The oauth response
     */
    public function connect(UserInterface $user, UserResponseInterface $response);
}



FOS\Bundle\OAuthSocialConnect
