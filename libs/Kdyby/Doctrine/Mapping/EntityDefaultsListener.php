<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Mapping;

use Doctrine;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Kdyby;
use Nette;
use Nette\Reflection\ClassType;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class EntityDefaultsListener extends Nette\Object implements Doctrine\Common\EventSubscriber
{

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
	 * @throws \Kdyby\InvalidStateException
	 */
	public function loadClassMetadata(LoadClassMetadataEventArgs $args)
	{
		$meta = $args->getClassMetadata();
		if ($meta->isMappedSuperclass) {
			return;
		}

		if (!$meta->customRepositoryClassName) {
			$meta->setCustomRepositoryClass('Kdyby\Doctrine\Dao');
		}

		$refl = new ClassType($meta->customRepositoryClassName);
		if (!$refl->implementsInterface('Kdyby\Persistence\IDao')) {
			throw new Kdyby\InvalidStateException("Your repository class for entity '" . $meta->name . "' should extend 'Kdyby\\Doctrine\\Dao'.");
		}
	}

}
