<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\ResourceBundle\DependencyInjection\Compiler;

use Sylius\Bundle\ResourceBundle\ResourceBundleInterface;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds all resources to the registry.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class ResolveDynamicRelationsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $resources = $container->getParameter('sylius.resources');

        if (!$container->hasDefinition('doctrine.orm.listeners.resolve_target_entity')) {
            throw new \RuntimeException('Cannot find Doctrine Target Entity Resolver Listener.');
        }

        $resolveTargetEntityListener = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');

        foreach ($resources as $resourceName => $resourceConfig) {
            if (SyliusResourceBundle::DRIVER_DOCTRINE_ORM !== $resourceConfig['driver']) {
                continue;
            }

            if (!isset($resourceConfig['classes']['interface']))
                echo $resourceConfig['classes']['model'];

            $resolveTargetEntityListener
                ->addMethodCall('addResolveTargetEntity', array(
                    $this->getInterface($container, $resourceConfig['classes']['interface']),
                    $this->getClass($container, $resourceConfig['classes']['model']),
                    array(),
                ));
        }

        if (!$resolveTargetEntityListener->hasTag('doctrine.event_listener')) {
            $resolveTargetEntityListener->addTag('doctrine.event_listener', array('event' => 'loadClassMetadata'));
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string $key
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function getInterface(ContainerBuilder $container, $key)
    {
        if ($container->hasParameter($key)) {
            return $container->getParameter($key);
        }

        if (interface_exists($key)) {
            return $key;
        }

        throw new \InvalidArgumentException(
            sprintf('The interface %s does not exist.', $key)
        );
    }

    /**
     * @param ContainerBuilder $container
     * @param string $key
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function getClass(ContainerBuilder $container, $key)
    {
        if ($container->hasParameter($key)) {
            return $container->getParameter($key);
        }

        if (class_exists($key)) {
            return $key;
        }

        throw new \InvalidArgumentException(
            sprintf('The class %s does not exist.', $key)
        );
    }
}