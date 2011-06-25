<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ODM;

use Doctrine\CouchDB\CouchDBClient;
use Doctrine\ODM\CouchDB\DocumentManager;
use Kdyby;
use Nette;
use Symfony\Component\Console\Helper\Helper;



/**
 * Doctrine CLI Connection Helper.
 *
 * @author Filip Procházka
 */
class CouchDBHelper extends Helper
{

	/** @var DocumentManager */
	protected $dm;

	/** @var CouchDBClient */
	protected $couchDBClient;



	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->dm = $container->getDocumentManager();
		$this->couchDBClient = $this->dm->getCouchDBClient();
	}



	/**
	 * @return DocumentManager
	 */
	public function getDocumentManager()
	{
		return $this->dm;
	}



	/**
	 * @return CouchDBClient
	 */
	public function getCouchDBClient()
	{
		return $this->couchDBClient;
	}



	/**
	 * @see Helper
	 */
	public function getName()
	{
		return 'couchdb';
	}

}
