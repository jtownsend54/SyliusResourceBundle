<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\ResourceBundle\DependencyInjection\Driver;

use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Sylius\Component\Resource\Exception\Driver\UnknownDriverException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Arnaud Langlade <aRn0D.dev@gmail.com>
 */
class DatabaseDriverFactory
{
    public static function get(
        $type = SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
        ContainerBuilder $container,
        $prefix,
        $resourceName,
        $objectManagerName,
        $templates = null
    ) {

        // support custom driver by having the type be a FQCN
        if (class_exists($type)) {
            return new $type($container, $prefix, $resourceName, $objectManagerName, $templates);
        }

        switch ($type) {
            case SyliusResourceBundle::DRIVER_DOCTRINE_ORM:
                return new DoctrineORMDriver($container, $prefix, $resourceName, $objectManagerName, $templates);
            case SyliusResourceBundle::DRIVER_DOCTRINE_MONGODB_ODM:
                return new DoctrineODMDriver($container, $prefix, $resourceName, $objectManagerName, $templates);
            case SyliusResourceBundle::DRIVER_DOCTRINE_PHPCR_ODM:
                return new DoctrinePHPCRDriver($container, $prefix, $resourceName, $objectManagerName, $templates);
            default:
                throw new UnknownDriverException($type);
        }
    }
}
