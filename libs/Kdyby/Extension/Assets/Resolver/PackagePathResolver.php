<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets\Resolver;

use Kdyby;
use Kdyby\Packages\PackageManager;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class PackagePathResolver extends Nette\Object implements Kdyby\Extension\Assets\IResourceResolver
{

	/**
	 * @var \Kdyby\Packages\PackageManager
	 */
	private $packageManager;



	/**
	 * @param \Kdyby\Packages\PackageManager $packageManager
	 */
	public function __construct(PackageManager $packageManager)
	{
		$this->packageManager = $packageManager;
	}



	/**
	 * @param string $input
	 * @param array $options
	 *
	 * @return string|boolean
	 */
	public function locateResource($input, array &$options)
	{
		// expand bundle notation
		if ('@' !== $input[0] || strpos($input, '/') === FALSE) {
			return FALSE;
		}

		list($packageName) = explode('/', substr($input, 1), 2);
		$packagePath = $this->packageManager->getPackage($packageName)->getPath();

		// use the bundle path as this asset's root
		$options['root'] = array($packagePath . '/Resources/public');

		// canonicalize the input
		if (FALSE !== ($pos = strpos($input, '*'))) {
			list($before, $after) = explode('*', $input, 2);
			$input = $this->packageManager->locateResource($before) . '*' . $after;

		} else {
			$input = $this->packageManager->locateResource($input);
		}

		return $input;
	}

}
