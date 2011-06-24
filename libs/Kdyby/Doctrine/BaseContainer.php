<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Kdyby;
use Kdyby\DI\Container;
use Nette;



/**
 * @author Filip Procházka
 *
 * @property-read Cache $cache
 */
abstract class BaseContainer extends Container
{

	/**
	 * Registers doctrine types
	 *
	 * @param Container $context
	 * @param array $parameters
	 */
	public function __construct(Container $context, $parameters = array())
	{
		$this->addService('context', $context);
		$this->addService('cache', $context->doctrineCache);
		$this->params += (array)$parameters;

		array_walk_recursive($this->params, function (&$value) use ($context) {
			$value = $context->expand($value);
		});
	}



	/**
	 * @param string $className
	 * @return bool
	 */
	abstract public function isManaging($className);



	/**
	 * @param string $className
	 * @return Doctrine\ORM\EntityRepository|Doctrine\ODM\CouchDB\DocumentRepository
	 */
	abstract public function getRepository($className);

}