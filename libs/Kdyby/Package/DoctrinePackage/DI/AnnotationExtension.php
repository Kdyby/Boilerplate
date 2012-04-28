<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
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
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('reader'))
			->setClass('Doctrine\Common\Annotations\AnnotationReader')
			->addSetup('addGlobalIgnoredName', array('serializationVersion'))
			->addSetup('addGlobalIgnoredName', array('todo:'));

		$builder->addDefinition($this->prefix('readerIndexed'))
			->setClass('Doctrine\Common\Annotations\IndexedReader', array($this->prefix('@reader')))
			->setInternal(TRUE)
			->setShared(FALSE);

		$builder->addDefinition($this->prefix('readerCached'))
			->setClass('Kdyby\Doctrine\Annotations\CachedReader', array(
				$this->prefix('@readerIndexed'), $this->prefix('@readerCached.cache')
			))
			->setInternal(TRUE)
			->setShared(FALSE);

		$builder->addDefinition($this->prefix('readerCached.cache'))
			->setClass('Kdyby\Doctrine\Cache', array('@kdyby.cacheStorage'))
			->setInternal(TRUE)
			->setShared(FALSE);
	}



	/**
	 * @param \Nette\Utils\PhpGenerator\ClassType $class
	 */
	public function afterCompile(Nette\Utils\PhpGenerator\ClassType $class)
	{
		/** @var \Nette\Utils\PhpGenerator\Method $init */
		$init = $class->methods['initialize'];

		// just look it up, mother fucker!
		$init->addBody('Doctrine\Common\Annotations\AnnotationRegistry::registerLoader("class_exists");');
	}

}
