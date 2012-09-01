<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\EventDispatcher\DI;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class EventsExtension extends Nette\Config\CompilerExtension
{

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		dump(
			array_keys($this->findEvents()),
			array_keys($this->findSubscribers()),
			array_keys($this->findEventManagers())
		);
	}



	/**
	 * @return string[]
	 */
	protected function findEventManagers()
	{
		$builder = $this->getContainerBuilder();
		return array_merge(
			$builder->findByTag('eventManager'),
			iterator_to_array($this->findServicesByType('Doctrine\Common\EventManager'))
		);
	}



	/**
	 * @return string[]
	 */
	protected function findEvents()
	{
		$builder = $this->getContainerBuilder();
		return array_merge(
			$builder->findByTag('event'),
			iterator_to_array($this->findServicesByType('Kdyby\Extension\EventDispatcher\Event'))
		);
	}



	/**
	 * @return string[]
	 */
	protected function findSubscribers()
	{
		$builder = $this->getContainerBuilder();
		return array_merge(
			$builder->findByTag('doctrine.eventSubscriber'),
			$builder->findByTag('kdyby.eventSubscriber'),
			$builder->findByTag('eventSubscriber'),
			iterator_to_array($this->findServicesByType('Doctrine\Common\EventSubscriber'))
		);
	}



	/**
	 * @param string $type
	 *
	 * @return \Nette\Iterators\Filter
	 */
	private function findServicesByType($type)
	{
		$definitions = new \ArrayIterator($this->getContainerBuilder()->getDefinitions());
		return new Nette\Iterators\Filter($definitions, function (Nette\DI\ServiceDefinition $def) use ($type)
		{
			if (!$def->class || !class_exists($def->class) || !interface_exists($def->class)) {
				return FALSE; // nothing to check
			}

			if (class_exists($type)) {
				return in_array($type, class_parents($def->class) + array($def->class));
			}

			if (interface_exists($type)) {
				return in_array($type, class_implements($def->class));
			}

			return FALSE;
		});
	}

}
