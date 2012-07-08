<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Mapping\Driver;

use Doctrine;
use Doctrine\ORM\Mapping\MappingException;
use Kdyby;
use Nette;
use Nette\Reflection\ClassType;
use Nette\Utils\Finder;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AnnotationDriver extends Doctrine\ORM\Mapping\Driver\AnnotationDriver
{

	const IGNORE_FOLDERS = '.noentities';



	/**
	 * {@inheritdoc}
	 *
	 * @param \Doctrine\Common\Annotations\AnnotationReader $reader The AnnotationReader to use, duck-typed.
	 * @param string|array $paths One or multiple paths where mapping classes can be found.
	 */
	public function __construct(Doctrine\Common\Annotations\Reader $reader, $paths = null)
	{
		parent::__construct($reader, $paths);
	}



	/**
	 * @param array $classNames
	 */
	public function setClassNames(array $classNames)
	{
		$this->_classNames = $classNames;
	}



	/**
	 * @return \Nette\Utils\Finder
	 */
	private function createFilesIterator()
	{
		return Finder::findFiles('*' . $this->_fileExtension)->from($this->_paths)->filter(function ($directory) {
			if (!$directory->isDir()) {
				return FALSE;
			}

			if (glob($directory->getPathname() . '/' . AnnotationDriver::IGNORE_FOLDERS)) {
				return FALSE;
			}

			return TRUE;
		});
	}



	/**
	 * {@inheritDoc}
	 */
	public function getAllClassNames()
	{
		if ($this->_classNames !== null) {
			return $this->_classNames;
		}

		if (!$this->_paths) {
			throw MappingException::pathRequired();
		}

		$classes = array();
		$includedFiles = array();

		foreach ($this->_paths as $path) {
			if (!is_dir($path)) {
				throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
			}
		}

		foreach ($this->createFilesIterator() as $file) {
			$sourceFile = realpath($file->getPathName());
			require_once $sourceFile;
			$includedFiles[] = $sourceFile;
		}

		$declared = get_declared_classes();
		foreach ($declared as $className) {
			$rc = new \ReflectionClass($className);
			$sourceFile = $rc->getFileName();
			if (in_array($sourceFile, $includedFiles) && ! $this->isTransient($className)) {
				$classes[] = $className;
			}
		}

		$this->_classNames = $classes;

		return $classes;
	}

}
