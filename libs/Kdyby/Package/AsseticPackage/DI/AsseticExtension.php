<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\AsseticPackage\DI;

use Kdyby;
use Nette;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AsseticExtension extends Kdyby\Config\CompilerExtension
{
	/** @var array */
	public $asseticDefaults = array(
		'publicDir' => '%wwwDir%',
		'prefix' => 'static',
		'debug' => '%kdyby.debug%'
	);



	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig();

		$options = self::getOptions($config, $this->asseticDefaults);
		$options['output'] = $options['prefix'] . '/*';
		$container->parameters += array(
			'assetic.debug' => $debug = (bool)$container->expand($options['debug']),
			'assetic.outputMask' => $options['output']
		);

		if ($debug) {
			$container->addDefinition($this->prefix('assetStorage'))
				->setClass('Kdyby\Assets\Storage\CacheStorage', array(
				'@kdyby.cacheStorage', '%tempDir%/cache', '@httpRequest'
			));

			$container->addDefinition('asseticPackage.asseticPresenter')
				->setClass('Kdyby\Package\AsseticPackage\Presenter\AsseticPresenter', array('@assetic.assetStorage'))
				->setParameters(array());

			$container->addDefinition($this->prefix('route.asset'))
				->setClass('Nette\Application\Routers\Route', array('<prefix ' . $options['prefix'] . '>/<name .*>', array(
					'presenter' => 'AsseticPackage:Assetic',
				)))
				->setAutowired(FALSE)
				->addTag('route', array('priority' => 100));

		} else {
			$container->addDefinition($this->prefix('assetStorage'))
				->setClass('Kdyby\Assets\Storage\PublicStorage', array(
				$options['publicDir'] . '/' . $options['prefix'], '@httpRequest'
			));
		}

		$container->addDefinition($this->prefix('filterManager'))
			->setClass('Kdyby\Assets\FilterManager', array('@container'));

		$container->addDefinition($this->prefix('assetManager'))
			->setClass('Kdyby\Assets\AssetManager');

		$container->addDefinition($this->prefix('assetFactory'))
			->setClass('Kdyby\Assets\AssetFactory', array(
				'@kdyby.packageManager', '@container', $options['publicDir']
			))
			->addSetup('setAssetManager', array($this->prefix('@assetManager')))
			->addSetup('setFilterManager', array($this->prefix('@filterManager')))
			->addSetup('setDefaultOutput', array($options['output']))
			->addSetup('setDebug', array($debug));

		$container->addDefinition($this->prefix('formulaeManager'))
			->setClass('Kdyby\Assets\FormulaeManager', array(
				$this->prefix('@assetStorage'), $this->prefix('@assetManager'), $this->prefix('@filterManager')
			))
			->addSetup('setDebug', array($debug));

		// macros
		$this->addMacro('macro.stylesheet', 'Kdyby\Assets\Latte\StylesheetMacro::install')
			->addSetup('setFactory', array($this->prefix('@assetFactory')));

		$this->addMacro('macro.javascript', 'Kdyby\Assets\Latte\JavascriptMacro::install')
			->addSetup('setFactory', array($this->prefix('@assetFactory')));
	}


	// todo: register filters by tags

}
