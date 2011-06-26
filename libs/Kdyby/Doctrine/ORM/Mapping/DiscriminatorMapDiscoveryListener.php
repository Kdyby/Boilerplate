<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ORM\Mapping;

use Doctrine;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class DiscriminatorMapDiscoveryListener extends Nette\Object implements Doctrine\Common\EventSubscriber
{

	/** @var Reader */
	private $annotationsReader;



	/**
	 * @param Reader $reader
	 */
	public function __construct(Reader $reader)
	{
		$this->annotationsReader = $reader;
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
	 * @param LoadClassMetadataEventArgs $args
	 */
	public function loadClassMetadata(LoadClassMetadataEventArgs $args)
	{
		$meta = $args->getClassMetadata();
		$entry = $this->annotationsReader->getClassAnnotation(
				$meta->getReflectionClass(),
				'Doctrine\ORM\Mapping\DiscriminatorEntry'
			);

		if ($entry === NULL) {
			return;
		}

		$em = $args->getEntityManager();
		foreach ($meta->parentClasses as $parent) {
			$parentMeta = $em->getClassMetadata($parent);
			$map = $parentMeta->discriminatorMap + array(
				$entry->name => $meta->name
			);

			if ($parentMeta->inheritanceType === ClassMetadataInfo::INHERITANCE_TYPE_NONE) {
				continue;
			}

			$parentMeta->setDiscriminatorMap($map);
			$meta->setDiscriminatorMap($map);
			$parentMeta->subClasses = array_unique($parentMeta->subClasses);
		}
	}

}