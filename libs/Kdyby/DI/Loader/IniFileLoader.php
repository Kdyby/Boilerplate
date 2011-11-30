<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI\Loader;

use Symfony;
use Kdyby;
use Kdyby\DI\FileLoaderImportLogger;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class IniFileLoader extends Symfony\Component\DependencyInjection\Loader\IniFileLoader
{

	/** @var \Kdyby\DI\FileLoaderImportLogger */
	private $logger;



	/**
	 * @param \Kdyby\DI\FileLoaderImportLogger $logger
	 */
	public function setLogger(FileLoaderImportLogger $logger)
	{
		$this->logger = $logger;
	}



	/**
	 * Imports a resource.
	 *
	 * @param mixed $resource
	 * @param string $type
	 * @param bool $ignoreErrors
	 * @param string $sourceResource
	 *
	 * @return mixed
	 */
	public function import($resource, $type = NULL, $ignoreErrors = false, $sourceResource = null)
	{
		if ($this->logger !== NULL) {
			$this->logger->log($resource, $type, $ignoreErrors, $sourceResource);
		}

		return parent::import($resource, $type, $ignoreErrors, $sourceResource);
	}

}
