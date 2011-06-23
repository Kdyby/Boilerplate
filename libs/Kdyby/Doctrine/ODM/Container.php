<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ODM;

use Doctrine\ODM\CouchDB\DocumentManager;
use Doctrine\ODM\CouchDB\DocumentRepository;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 *
 * @property-read Kdyby\DI\Container $context
 * @property-read DocumentManager documentManager
 */
class Container extends Kdyby\DI\Container implements Kdyby\Doctrine\IContainer
{

	/** @var array */
	public $params = array(
			'entityDirs' => array('%appDir%', '%kdybyDir%'),
			'listeners' => array(),
		);



	/**
	 * Registers doctrine types
	 *
	 * @param Kdyby\DI\Container $context
	 * @param array $parameters
	 */
	public function __construct(Kdyby\DI\Container $context, $parameters = array())
	{
		throw new Nette\NotImplementedException; // todo: remove

		$this->addService('context', $context);
		$this->params += (array)$parameters;

		array_walk_recursive($this->params, function (&$value) use ($context) {
			$value = $context->expand($value);
		});
	}



	/**
	 * @return DocumentManager
	 */
	public function getDocumentManager()
	{
		return $this->documentManager;
	}



	/**
	 * @param string $documentName
	 * @return DocumentRepository
	 */
	public function getRepository($documentName)
	{
		return $this->getDocumentManager()->getRepository($documentName);
	}



	/**
	 * @param string $className
	 * @return bool
	 */
	public function isManaging($className)
	{
		return $this->getDocumentManager()->getMetadataFactory()->hasMetadataFor($className);
	}

}