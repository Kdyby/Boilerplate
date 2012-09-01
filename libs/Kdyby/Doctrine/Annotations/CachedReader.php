<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Annotations;

use Doctrine;
use Doctrine\Common\Annotations\Reader;
use Kdyby\Doctrine\Cache;
use Kdyby;
use Nette;
use Nette\Reflection\ClassType;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class CachedReader extends Nette\Object implements Reader
{
	private static $CACHE_SALT = '@[Annot]';

	/** @var Reader */
	private $delegate;

	/** @var Cache */
	private $cache;

	/** @var array */
	private $loadedAnnotations;



	/**
	 * @param Reader $reader
	 * @param Cache $cache
	 */
	public function __construct(Reader $reader, Cache $cache)
	{
		$this->delegate = $reader;
		$this->cache = $cache;
	}



	/**
	 * @param \ReflectionClass $class
	 * @return array
	 */
	public function getClassAnnotations(\ReflectionClass $class)
	{
		$cacheKey = $class->getName() . self::$CACHE_SALT;

		if (isset($this->loadedAnnotations[$cacheKey])) {
			return $this->loadedAnnotations[$cacheKey];
		}

		if (($annots = $this->cache->fetch($cacheKey)) !== FALSE) {
			return $annots;
		}

		$annots = $this->delegate->getClassAnnotations($class);
		$this->cache->saveDependingOnFiles($cacheKey, $annots, $this->getClassDefinitionFiles($class));

		return $this->loadedAnnotations[$cacheKey] = $annots;
	}



	/**
	 * @param \ReflectionClass $class
	 * @param string $annotationName
	 * @return Doctrine\Common\Annotations\Annotation|NULL
	 */
	public function getClassAnnotation(\ReflectionClass $class, $annotationName)
	{
		foreach ($this->getClassAnnotations($class) as $annot) {
			if ($annot instanceof $annotationName) {
				return $annot;
			}
		}

		return NULL;
	}



	/**
	 * @param \ReflectionProperty $property
	 * @return array
	 */
	public function getPropertyAnnotations(\ReflectionProperty $property)
	{
		$class = $property->getDeclaringClass();
		$cacheKey = $class->getName() . '$' . $property->getName() . self::$CACHE_SALT;

		if (isset($this->loadedAnnotations[$cacheKey])) {
			return $this->loadedAnnotations[$cacheKey];
		}

		if (($annots = $this->cache->fetch($cacheKey)) !== FALSE) {
			return $annots;
		}

		$annots = $this->delegate->getPropertyAnnotations($property);
		$this->cache->saveDependingOnFiles($cacheKey, $annots, $this->getClassDefinitionFiles($class));

		return $this->loadedAnnotations[$cacheKey] = $annots;
	}



	/**
	 * @param \ReflectionProperty $property
	 * @param string $annotationName
	 * @return Doctrine\Common\Annotations\Annotation|NULL
	 */
	public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName)
	{
		foreach ($this->getPropertyAnnotations($property) as $annot) {
			if ($annot instanceof $annotationName) {
				return $annot;
			}
		}

		return NULL;
	}



	/**
	 * @param \ReflectionMethod $method
	 * @return array
	 */
	public function getMethodAnnotations(\ReflectionMethod $method)
	{
		$class = $method->getDeclaringClass();
		$cacheKey = $class->getName() . '#' . $method->getName() . self::$CACHE_SALT;

		if (isset($this->loadedAnnotations[$cacheKey])) {
			return $this->loadedAnnotations[$cacheKey];
		}

		if (($annots = $this->cache->fetch($cacheKey)) !== FALSE) {
			return $annots;
		}

		$annots = $this->delegate->getMethodAnnotations($method);
		$this->cache->saveDependingOnFiles($cacheKey, $annots, $this->getClassDefinitionFiles($class));

		return $this->loadedAnnotations[$cacheKey] = $annots;
	}



	/**
	 * @param \ReflectionMethod $method
	 * @param string $annotationName
	 * @return Doctrine\Common\Annotations\Annotation|NULL
	 */
	public function getMethodAnnotation(\ReflectionMethod $method, $annotationName)
	{
		foreach ($this->getMethodAnnotations($method) as $annot) {
			if ($annot instanceof $annotationName) {
				return $annot;
			}
		}

		return NULL;
	}



	/**
	 * Clears internal array cache
	 */
	public function clearLoadedAnnotations()
	{
		$this->loadedAnnotations = array();
	}



	/**
	 * @param \ReflectionClass $class
	 * @return array
	 */
	private function getClassDefinitionFiles(\ReflectionClass $class)
	{
		$class = ClassType::from($class->getName());

		$files = array($class->getFileName());
		while ($class = $class->getParentClass()) {
			$files[] = $class->getFileName();
		}

		return $files;
	}

}
