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

	/**
	 * Registers doctrine types
	 *
	 * @param Kdyby\DI\Container $context
	 */
	public function __construct(Kdyby\DI\Container $context)
	{
		throw new Nette\NotImplementedException;
		$this->addService('context', $context);
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