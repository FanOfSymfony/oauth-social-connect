<?php

namespace FOS\Bundle\OAuthSocialConnectBundle;

use FOS\Bundle\OAuthSocialConnectBundle\DependencyInjection\CompilerPass\SetResourceOwnerServiceNameCompilerPass;
use FOS\Bundle\OAuthSocialConnectBundle\DependencyInjection\FOSOAuthSocialConnectExtension;
use FOS\Bundle\OAuthSocialConnectBundle\DependencyInjection\Security\Factory\OAuthFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FOSOAuthSocialConnectBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        /** @var $extension SecurityExtension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new OAuthFactory());

        $container->addCompilerPass(new SetResourceOwnerServiceNameCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        // return the right extension instead of "auto-registering" it. Now the
        // alias can be FOS_oauth instead of FOS_o_auth..
        if (null === $this->extension) {
            return new FOSOAuthSocialConnectExtension();
        }

        return $this->extension;
    }
}
