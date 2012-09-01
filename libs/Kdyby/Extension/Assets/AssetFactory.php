<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets;

use Assetic;
use Kdyby;
use Nette;
use Nette\DI\Container;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class AssetFactory extends Assetic\Factory\AssetFactory
{

	/**
	 * @var \SystemContainer|\Nette\DI\Container
	 */
	private $container;

	/**
	 * @var array|\Kdyby\Extension\Assets\IResourceResolver
	 */
	private $resolvers = array();



	/**
	 * @param \Nette\DI\Container $container
	 * @param string $baseDir
	 */
	public function __construct(Container $container, $baseDir)
	{
		$this->container = $container;
		parent::__construct($baseDir, FALSE);
	}



	/**
	 * @param \Kdyby\Extension\Assets\IResourceResolver $resolver
	 */
	public function addResolver(IResourceResolver $resolver)
	{
		$this->resolvers[] = $resolver;
	}



	/**
	 * Adds support parameter placeholders and resource resolvers.
	 *
	 * @param string $input
	 * @param array $options
	 *
	 * @return \Assetic\Asset\AssetInterface
	 */
	protected function parseInput($input, array $options = array())
	{
		$input = $this->container->expand($input);

		foreach ($this->resolvers as $resolver) {
			/** @var IResourceResolver $resolver */
			if (($resolved = $resolver->locateResource($input, $options)) !== FALSE) {
				$input = $resolved;
				break;
			}
		}

		return parent::parseInput($input, $options);
	}

}
