<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Mapping;

use Doctrine;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\Driver;
use Kdyby;
use Nette;
use Nette\Reflection\ClassType;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DiscriminatorMapDiscoveryListener extends Nette\Object implements Doctrine\Common\EventSubscriber
{

	/** @var \Doctrine\Common\Annotations\Reader */
	private $reader;

	/** @var \Doctrine\ORM\Mapping\Driver\Driver */
	private $driver;



	/**
	 * @param \Doctrine\Common\Annotations\Reader $reader
	 * @param \Doctrine\ORM\Mapping\Driver\Driver $driver
	 */
	public function __construct(Reader $reader, Driver $driver)
	{
		$this->reader = $reader;
		$this->driver = $driver;
	}



	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			Events::loadClassMetadata,
		);
	}



	/**
	 * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $args
	 */
	public function loadClassMetadata(LoadClassMetadataEventArgs $args)
	{
		$meta = $args->getClassMetadata();

		if ($meta->isInheritanceTypeNone()) {
			return;
		}

		$map = $meta->discriminatorMap;
		foreach ($this->getChildClasses($meta->name) as $className) {
			if (!in_array($className, $meta->discriminatorMap) && $entry = $this->getEntryName($className)) {
				$map[$entry->name] = $className;
			}
		}

		$meta->setDiscriminatorMap($map);
		$meta->subClasses = array_unique($meta->subClasses);
	}



	/**
	 * @param string $currentClass
	 *
	 * @return array
	 */
	private function getChildClasses($currentClass)
	{
		$classes = array();
		foreach ($this->driver->getAllClassNames() as $className) {
			if (!ClassType::from($className)->isSubclassOf($currentClass)) {
				continue;
			}

			$classes[] = $className;
		}
		return $classes;
	}



	/**
	 * @param string $className
	 *
	 * @return string|NULL
	 */
	private function getEntryName($className)
	{
		return $this->reader->getClassAnnotation(
			ClassType::from($className),
			'Doctrine\ORM\Mapping\DiscriminatorEntry'
		) ? : NULL;
	}

}
