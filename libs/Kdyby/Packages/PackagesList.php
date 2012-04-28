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



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PackagesList extends Nette\Object implements IPackageList
{

	/** @var array */
	private $packages;



	/**
	 * @param array $packages
	 */
	public function __construct(array $packages)
	{
		$this->packages = $packages;
	}



	/**
	 * @return string[]
	 */
	public function getPackages()
	{
		return $this->packages;
	}
}
