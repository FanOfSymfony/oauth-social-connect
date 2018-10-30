<?php

namespace FOS\Bundle\OAuthSocialConnectBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SetResourceOwnerServiceNameCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach (array_keys($container->getAliases()) as $alias) {
            if (0 !== strpos($alias, 'fos_oauth_social_connect.resource_owner.')) {
                continue;
            }

            $aliasIdParts = explode('.', $alias);
            $resourceOwnerDefinition = $container->findDefinition($alias);
            $resourceOwnerDefinition->addMethodCall('setName', array(end($aliasIdParts)));
        }
    }
}
