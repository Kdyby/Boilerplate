<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\DoctrinePackage\DI;

use Kdyby;
use Nette;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;



/**
 * DbalExtension is an extension for the Doctrine DBAL library.
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AnnotationExtension extends Kdyby\Config\CompilerExtension
{

	/**
	 * annotation:
	 * 	aliases:
	 * 		Orm: Doctrine\ORM\Mapping
	 */
	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$container->addDefinition($this->prefix('reader'))
			->setClass('Doctrine\Common\Annotations\AnnotationReader')
			->addSetup('addGlobalIgnoredName', array('serializationVersion'))
			->addSetup('addGlobalIgnoredName', array('todo:'));

		$container->addDefinition($this->prefix('readerIndexed'))
			->setClass('Doctrine\Common\Annotations\IndexedReader', array($this->prefix('@reader')))
			->setInternal(TRUE)
			->setShared(FALSE);

		$container->addDefinition($this->prefix('readerCached'))
			->setClass('Kdyby\Doctrine\Annotations\CachedReader', array(
				$this->prefix('@readerIndexed'), $this->prefix('@readerCached.cache')
			))
			->setInternal(TRUE)
			->setShared(FALSE);

		$container->addDefinition($this->prefix('readerCached.cache'))
			->setClass('Kdyby\Doctrine\Cache', array('@kdyby.cacheStorage'))
			->setInternal(TRUE)
			->setShared(FALSE);
	}

}
