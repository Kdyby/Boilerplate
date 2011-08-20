<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ODM;

use Doctrine;
use Doctrine\ODM\CouchDB\DocumentManager;
use Doctrine\ODM\CouchDB\DocumentRepository;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 *
 * @property-read Kdyby\DI\Container $context
 * @property-read DocumentManager $documentManager
 * @property-read Doctrine\CouchDB\HTTP\SocketClient $httpClient
 * @property-read Doctrine\CouchDB\CouchDBClient $couchClient
 * @property-read Doctrine\Common\Annotations\AnnotationReader $annotationReader
 * @property-read Doctrine\ODM\CouchDB\Mapping\Driver\AnnotationDriver $annotationDriver
 * @property-read \Doctrine\ODM\CouchDB\Configuration $configuration
 */
class Container extends Kdyby\Doctrine\BaseContainer
{

	/** @var array */
	public $params = array(
			'documentDirs' => array('%appDir%', '%kdybyFrameworkDir%'),
			'listeners' => array(),
		);



	/**
	 * @return Doctrine\CouchDB\HTTP\SocketClient
	 */
	protected function createServiceHttpClient()
	{
		return new Doctrine\CouchDB\HTTP\SocketClient();
	}



	/**
	 * @return Doctrine\Common\Annotations\AnnotationReader
	 */
	protected function createServiceAnnotationReader()
	{
		$reader = new Doctrine\Common\Annotations\AnnotationReader();
		$reader->setDefaultAnnotationNamespace('Doctrine\ODM\CouchDB\Mapping\\');

		return $reader;
	}



	/**
	 * @return Mapping\Driver\AnnotationDriver
	 */
	protected function createServiceAnnotationDriver()
	{
		$reader = new Doctrine\Common\Annotations\CachedReader(
			new Doctrine\Common\Annotations\IndexedReader($this->annotationReader),
			$this->hasService('annotationCache') ? $this->annotationCache : $this->cache
		);

		return new Mapping\Driver\AnnotationDriver($this->annotationReader, $this->params['documentDirs']);
	}



	/**
	 * @return Doctrine\CouchDB\CouchDBClient
	 */
	protected function createServiceCouchClient()
	{
		return new Doctrine\CouchDB\CouchDBClient($this->httpClient, $this->params['database']);
	}



	/**
	 * @return Doctrine\ODM\CouchDB\Configuration
	 */
	protected function createServiceConfiguration()
	{
		$config = new Doctrine\ODM\CouchDB\Configuration();

		$config->setMetadataDriverImpl($this->annotationDriver);
		$config->setLuceneHandlerName('_fti');

		$config->setProxyDir($this->params['proxiesDir']);
		$config->setProxyNamespace($this->getParam('proxyNamespace', 'Kdyby\Domain\Proxies'));

		return $config;
	}



	/**
	 * @return DocumentManager
	 */
	protected function createServiceDocumentManager()
	{
		return DocumentManager::create($this->couchClient, $this->configuration);
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