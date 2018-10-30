<?php

namespace FOS\Bundle\OAuthSocialConnectBundle\DependencyInjection;

use FOS\Bundle\OAuthSocialConnectBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class FOSOAuthSocialConnectExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     * @throws \RuntimeException
     * @throws InvalidConfigurationException
     * @throws \Symfony\Component\DependencyInjection\Exception\BadMethodCallException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\OutOfBoundsException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/'));
        $loader->load('http_client.xml');
        $loader->load('oauth.xml');
        $loader->load('templating.xml');
        $loader->load('twig.xml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $this->createHttplugClient($container, $config);

        // set current firewall
        if (empty($config['firewall_names'])) {
            throw new InvalidConfigurationException('The child node "firewall_names" at path "fos_oauth_social_connect" must be configured.');
        }
        $container->setParameter('fos_oauth_social_connect.firewall_names', $config['firewall_names']);

        // set target path parameter
        $container->setParameter('fos_oauth_social_connect.target_path_parameter', $config['target_path_parameter']);

        // set use referer parameter
        $container->setParameter('fos_oauth_social_connect.use_referer', $config['use_referer']);

        // set failed use referer parameter
        $container->setParameter('fos_oauth_social_connect.failed_use_referer', $config['failed_use_referer']);

        // set failed auth path
        $container->setParameter('fos_oauth_social_connect.failed_auth_path', $config['failed_auth_path']);

        // set grant rule
        $container->setParameter('fos_oauth_social_connect.grant_rule', $config['grant_rule']);

        // setup services for all configured resource owners
        $resourceOwners = array();
        foreach ($config['resource_owners'] as $name => $options) {
            $resourceOwners[$name] = $name;
            $this->createResourceOwnerService($container, $name, $options);
        }
        $container->setParameter('fos_oauth_social_connect.resource_owners', $resourceOwners);

        $oauthUtils = $container->getDefinition('fos_oauth_social_connect.security.oauth_utils');
        foreach ($config['firewall_names'] as $firewallName) {
            $oauthUtils->addMethodCall('addResourceOwnerMap', array(new Reference('fos_oauth_social_connect.resource_ownermap.'.$firewallName)));
        }

        $this->createConnectIntegration($container, $config);

        $container->setAlias('fos_oauth_social_connect.user_checker', new Alias('security.user_checker', true));
    }

    /**
     * Creates a resource owner service.
     *
     * @param ContainerBuilder $container The container builder
     * @param string           $name      The name of the service
     * @param array            $options   Additional options of the service
     *
     * @throws InvalidConfigurationException
     * @throws \Symfony\Component\DependencyInjection\Exception\BadMethodCallException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function createResourceOwnerService(ContainerBuilder $container, $name, array $options)
    {
        $definitionClassname = $this->getDefinitionClassname();

        // alias services
        if (isset($options['service'])) {
            // set the appropriate name for aliased services, compiler pass depends on it
            $container->setAlias('fos_oauth_social_connect.resource_owner.'.$name, new Alias($options['service'], true));

            return;
        }

        $type = $options['type'];
        unset($options['type']);

        // handle external resource owners with given class
        if (isset($options['class'])) {
            if (!is_subclass_of($options['class'], ResourceOwnerInterface::class)) {
                throw new InvalidConfigurationException(sprintf('Class "%s" must implement interface "FOS\Bundle\OAuthSocialConnectBundle\OAuth\ResourceOwnerInterface".', $options['class']));
            }

            $definition = new $definitionClassname('fos_oauth_social_connect.abstract_resource_owner.'.$type);
            $definition->setClass($options['class']);
            unset($options['class']);
        } else {
            $definition = new $definitionClassname('fos_oauth_social_connect.abstract_resource_owner.'.Configuration::getResourceOwnerType($type));
            $definition->setClass("%fos_oauth_social_connect.resource_owner.$type.class%");
        }

        $definition->replaceArgument(2, $options);
        $definition->replaceArgument(3, $name);
        $definition->setPublic(true);

        $container->setDefinition('fos_oauth_social_connect.resource_owner.'.$name, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'fos_oauth_social_connect';
    }

    /**
     * Check of the connect controllers etc should be enabled.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\BadMethodCallException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    private function createConnectIntegration(ContainerBuilder $container, array $config)
    {
        $definitionClassname = $this->getDefinitionClassname();

        if (isset($config['connect'])) {
            $container->setParameter('fos_oauth_social_connect.connect', true);

            if (isset($config['fosub'])) {
                $container->setParameter('fos_oauth_social_connect.fosub_enabled', true);

                $definition = $container->setDefinition('fos_oauth_social_connect.user.provider.fosub_bridge', new $definitionClassname('fos_oauth_social_connect.user.provider.fosub_bridge.def'));
                $definition->addArgument($config['fosub']['properties']);

                // setup fosub bridge services
                $container->setAlias('fos_oauth_social_connect.account.connector', new Alias('fos_oauth_social_connect.user.provider.fosub_bridge', true));

                $definition = $container->setDefinition('fos_oauth_social_connect.registration.form.handler.fosub_bridge', new $definitionClassname('fos_oauth_social_connect.registration.form.handler.fosub_bridge.def'));
                $definition->addArgument($config['fosub']['username_iterations']);

                $container->setAlias('fos_oauth_social_connect.registration.form.handler', new Alias('fos_oauth_social_connect.registration.form.handler.fosub_bridge', true));

                // enable compatibility with FOSUserBundle 1.3.x and 2.x
                if (interface_exists('FOS\UserBundle\Form\Factory\FactoryInterface')) {
                    $container->setAlias('fos_oauth_social_connect.registration.form.factory', new Alias('fos_user.registration.form.factory', true));
                } else {
                    // FOSUser 1.3 BC. To be removed.
                    $definition->setScope('request');

                    $container->setAlias('fos_oauth_social_connect.registration.form', new Alias('fos_user.registration.form', true));
                }
            } else {
                $container->setParameter('fos_oauth_social_connect.fosub_enabled', false);
            }

            foreach ($config['connect'] as $key => $serviceId) {
                if ('confirmation' === $key) {
                    $container->setParameter('fos_oauth_social_connect.connect.confirmation', $config['connect']['confirmation']);

                    continue;
                }

                $container->setAlias('fos_oauth_social_connect.'.str_replace('_', '.', $key), new Alias($serviceId, true));
            }
        } else {
            $container->setParameter('fos_oauth_social_connect.fosub_enabled', false);
            $container->setParameter('fos_oauth_social_connect.connect', false);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function createHttplugClient(ContainerBuilder $container, array $config)
    {
        $httpClientId = $config['http']['client'];
        $httpMessageFactoryId = $config['http']['message_factory'];
        $bundles = $container->getParameter('kernel.bundles');

        if ('httplug.client.default' === $httpClientId && !isset($bundles['HttplugBundle'])) {
            throw new InvalidConfigurationException(
                'You must setup php-http/httplug-bundle to use the default http client service.'
            );
        }
        if ('httplug.message_factory.default' === $httpMessageFactoryId && !isset($bundles['HttplugBundle'])) {
            throw new InvalidConfigurationException(
                'You must setup php-http/httplug-bundle to use the default http message factory service.'
            );
        }

        $container->setAlias('fos_oauth_social_connect.http.client', new Alias($config['http']['client'], true));
        $container->setAlias('fos_oauth_social_connect.http.message_factory', new Alias($config['http']['message_factory'], true));
    }

    /**
     * @return string
     */
    private function getDefinitionClassname()
    {
        return class_exists(ChildDefinition::class) ? ChildDefinition::class : DefinitionDecorator::class;
    }
}
