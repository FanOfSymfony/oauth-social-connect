<?php

namespace FOS\Bundle\SOAuthSocialConnectorBundle;

use FOS\Bundle\OAuthBundle\DependencyInjection\CompilerPass\SetResourceOwnerServiceNameCompilerPass;
use FOS\Bundle\OAuthBundle\DependencyInjection\FOSOAuthExtension;
use FOS\Bundle\OAuthBundle\DependencyInjection\Security\Factory\OAuthFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FOSOAuthSocialConnectorEvents extends Bundle
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
            return new FOSOAuthExtension();
        }

        return $this->extension;
    }
}
ç
FOSOAuthSocialConnectorEvents
