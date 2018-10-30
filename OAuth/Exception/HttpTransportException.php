<?php

namespace FOS\Bundle\OAuthSocialConnectBundle\OAuth\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class HttpTransportException extends AuthenticationException
{
    private $ownerName;

    public function __construct($message, $ownerName, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->ownerName = $ownerName;
    }

    public function getOwnerName()
    {
        return $this->ownerName;
    }
}
