<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Packages\DoctrinePackage\DI\Compiler;

use Kdyby;
use Nette;
use Nette\Reflection\ClassType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class RegisterEventListenersAndSubscribersPass extends Nette\Object implements CompilerPassInterface
{

	/**
	 * @param ContainerBuilder $container
	 */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('doctrine')) {
            return;
        }

		$subscribers = $this->findSubscribersIds($container);
		foreach ($container->getParameter('doctrine.connections') as $connectionName => $connectionId) {
			$evm = $container->getDefinition('doctrine.dbal.' . $connectionName . '_connection.event_manager');

			foreach ($subscribers as $subscriber) {
				$evm->addMethodCall('addEventSubscriber', array($subscriber));
			}
        }
    }



	/**
	 * @param ContainerBuilder $container
	 * @return array
	 */
    private function findSubscribersIds(ContainerBuilder $container)
	{
		$bag = $container->getParameterBag();

		$subscribers = array();
		foreach ($container->getDefinitions() as $id => $definition) {
			$class = $definition->getClass();
			if ($definition->isAbstract() || !$class || !$class = $bag->resolveValue($class)) {
				continue;
			}

			try {
				if (!ClassType::from($class)->implementsInterface('Doctrine\\Common\\EventSubscriber')) {
					continue;
				}

			} catch (\ReflectionException $e) {
				continue;
			}

			$subscribers[] = new Reference($id);
		}

		return $subscribers;
	}

}
