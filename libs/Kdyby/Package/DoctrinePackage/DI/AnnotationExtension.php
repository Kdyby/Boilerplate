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
		$container = $this->getContainer();
		$config = $this->getConfig();
		
		$reader = $container->addDefinition('annotationReader')
			->setClass('Doctrine\Common\Annotations\AnnotationReader')
			->addSetup('setIgnoreNotImportedAnnotations', array(FALSE))
			->addSetup('setAnnotationNamespaceAlias', array('Doctrine\ORM\Mapping\\', 'Orm'))
			->addSetup('addGlobalIgnoredName', array('serializationVersion'));

		if (!empty($config['aliases'])) {
			foreach ($config['aliases'] as $alias => $namespace) {
				$reader->addSetup('setAnnotationNamespaceAlias', array(rtrim($namespace, '\\') . '\\', $alias));
			}
		}

		$container->addDefinition('annotationReader_indexed')
			->setClass('Doctrine\Common\Annotations\IndexedReader', array('@annotationReader'))
			->setInternal(TRUE)
			->setShared(FALSE);

		$container->addDefinition('annotationReader_cached')
			->setClass('Kdyby\Doctrine\Annotations\CachedReader', array('@annotationReader_indexed', '@annotationReader_cached_cache'))
			->setInternal(TRUE)
			->setShared(FALSE);

		$container->addDefinition('annotationReader_cached_cache')
			->setClass('Kdyby\Doctrine\Cache', array('@cacheStorage'))
			->setInternal(TRUE)
			->setShared(FALSE);
	}

}
