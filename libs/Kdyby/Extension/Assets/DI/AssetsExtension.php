<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Extension\Assets\DI;

use Kdyby;
use Nette;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AssetsExtension extends Kdyby\Config\CompilerExtension
{
	/** @var array */
	public $asseticDefaults = array(
		'publicDir' => '%wwwDir%',
		'prefix' => 'static',
		'debug' => '%kdyby.debug%'
	);



	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$options = $this->getConfig($this->asseticDefaults);
		$builder->parameters += array(
			'assets' => array(
				'debug' => $debug = (bool)$builder->expand($options['debug']),
				'prefix' => $options['prefix'],
				'outputMask' => $options['prefix'] . '/*',
				'publicDir' => $options['publicDir']
			)
		);

		if ($debug) {
			$builder->addDefinition($this->prefix('assetStorage'))
				->setClass('Kdyby\Extension\Assets\Storage\CacheStorage', array(
				'@kdyby.cacheStorage', '%tempDir%/cache', '@httpRequest'
			));

			$builder->addDefinition($this->prefix('route.asset'))
				->setClass('Kdyby\Extension\Assets\Router\AssetRoute', array('%assets.prefix%', $this->prefix('@assetStorage')))
				->setAutowired(FALSE);

			$builder->getDefinition('router')
				->addSetup('offsetSet', array(NULL, $this->prefix('@route.asset')));

		} else {
			$builder->addDefinition($this->prefix('assetStorage'))
				->setClass('Kdyby\Extension\Assets\Storage\PublicStorage', array(
					'%assets.publicDir%/%assets.prefix%', '@httpRequest'
				));
		}

		$builder->addDefinition($this->prefix('filterManager'))
			->setClass('Kdyby\Extension\Assets\FilterManager', array('@container'));

		$builder->addDefinition($this->prefix('assetManager'))
			->setClass('Kdyby\Extension\Assets\AssetManager');

		$builder->addDefinition($this->prefix('assetFactory'))
			->setClass('Kdyby\Extension\Assets\AssetFactory', array(
				'@kdyby.packageManager', '@container', '%assets.publicDir%'
			))
			->addSetup('setAssetManager', array($this->prefix('@assetManager')))
			->addSetup('setFilterManager', array($this->prefix('@filterManager')))
			->addSetup('setDefaultOutput', array('%assets.outputMask%'))
			->addSetup('setDebug', array('%assets.debug%'));

		$builder->addDefinition($this->prefix('formulaeManager'))
			->setClass('Kdyby\Extension\Assets\FormulaeManager', array(
				$this->prefix('@assetStorage'), $this->prefix('@assetManager'), $this->prefix('@filterManager')
			))
			->addSetup('setDebug', array('%assets.debug%'));

		// macros
		$builder->getDefinition('nette.latte')
			->addSetup('Kdyby\Extension\Assets\Latte\AssetMacros::install($service->compiler)->setFactory(?)', array(
				$this->prefix('@assetFactory')
			));
	}

	// todo: register filters by tags

}
