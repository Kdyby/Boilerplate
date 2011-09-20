<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine;

use Doctrine\Common\Cache\Cache as DoctrineCache;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
interface IContainerBuilder
{

	/**
	 * @param DoctrineCache $cache
	 */
	function setMetadataCache(DoctrineCache $cache);


	/**
	 * @param DoctrineCache $cache
	 */
	function setQueryCache(DoctrineCache $cache);


	/**
	 * @param DoctrineCache $cache
	 */
	function setAnnotationCache(DoctrineCache $cache);

	/**
	 * @return IContainer
	 */
	function build();

}