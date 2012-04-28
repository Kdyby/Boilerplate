<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Packages;

use Kdyby;
use Nette;
use Nette\Reflection\ClassType;
use Nette\Utils\Finder;
use Nette\Utils\Json;
use Nette\Utils\NeonException;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DirectoryPackages extends Nette\Object implements \IteratorAggregate, IPackageList
{

	/** @var string */
	private $dir;

	/** @var string */
	private $ns;



	/**
	 * @param string $dir
	 * @param string $ns
	 */
	public function __construct($dir, $ns = NULL)
	{
		$this->dir = realpath($dir);
		$this->ns = $ns;
	}



	/**
	 * @return array
	 */
	public function getPackages()
	{
		if (!is_dir($this->dir)) {
			return array();
		}

		$packages = array();
		foreach (Finder::findFiles('*Package.php')->from($this->dir) as $file) {
			$refl = $this->getClass($file);
			if ($this->isPackage($refl)) {
				$packages[] = $refl->getName();
			}
		}

		return $packages;
	}



	/**
	 * @param \Nette\Reflection\ClassType $refl
	 *
	 * @return bool
	 */
	protected function isPackage(ClassType $refl)
	{
		return $refl->isSubclassOf('Kdyby\Packages\Package') && !$refl->isAbstract();
	}



	/**
	 * @param \SplFileInfo $file
	 *
	 * @return \Nette\Reflection\ClassType
	 */
	protected function getClass(\SplFileInfo $file)
	{
		$class = $this->ns . '\\' . ltrim(substr($this->getRelative($file), 0, -4), '\\');
		if (!class_exists($class, FALSE)) {
			require_once $file->getRealpath();
		}

		return ClassType::from($class);
	}



	/**
	 * @param \SplFileInfo $file
	 *
	 * @return string
	 */
	protected function getRelative(\SplFileInfo $file)
	{
		return strtr($file->getRealpath(), array($this->dir => '', '/' => '\\'));
	}



	/**
	 * Retrieve an external iterator
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->getPackages());
	}

}
