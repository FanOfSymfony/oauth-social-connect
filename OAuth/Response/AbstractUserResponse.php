<?php

namespace FOS\Bundle\OAuthSocialConnectBundle\OAuth\Response;

use FOS\Bundle\OAuthSocialConnectBundle\OAuth\ResourceOwnerInterface;
use FOS\Bundle\OAuthSocialConnectBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
abstract class AbstractUserResponse implements UserResponseInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var ResourceOwnerInterface
     */
    protected $resourceOwner;

    /**
     * @var OAuthToken
     */
    protected $oAuthToken;

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getProfilePicture()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        return $this->oAuthToken->getAccessToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken()
    {
        return $this->oAuthToken->getRefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenSecret()
    {
        return $this->oAuthToken->getTokenSecret();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn()
    {
        return $this->oAuthToken->getExpiresIn();
    }

    /**
     * {@inheritdoc}
     */
    public function setOAuthToken(OAuthToken $token)
    {
        $this->oAuthToken = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getOAuthToken()
    {
        return $this->oAuthToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        if (is_array($data)) {
            $this->data = $data;
        } else {
            // First check that response exists, due too bug: https://bugs.php.net/bug.php?id=54484
            if (!$data) {
                $this->data = [];
            } else {
                $this->data = json_decode($data, true);

                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new AuthenticationException('Response is not a valid JSON code.');
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwner()
    {
        return $this->resourceOwner;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceOwner(ResourceOwnerInterface $resourceOwner)
    {
        $this->resourceOwner = $resourceOwner;
    }
}
