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
		'debug' => '%kdyby_debug%'
	);



	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param array $config
	 */
	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		$options = self::getOptions($config, $this->asseticDefaults);
		$options['output'] = $options['prefix'] . '/*';
		$container->parameters += array(
			'assetic_debug' => $debug = (bool)$container->expand($options['debug']),
			'assetic_outputMask' => $options['output']
		);

		if ($debug) {
			$container->addDefinition('assetic_assetStorage')
				->setClass('Kdyby\Assets\Storage\CacheStorage', array(
				'@cacheStorage', '%tempDir%/cache', '@httpRequest'
			));

			$container->addDefinition('asseticPackage_asseticPresenter')
				->setClass('Kdyby\Package\AsseticPackage\Presenter\AsseticPresenter', array('@assetic_assetStorage'))
				->setParameters(array());

			$container->addDefinition('assetic_route_asset')
				->setClass('Nette\Application\Routers\Route', array('/<prefix ' . $options['prefix'] . '>/<name .*>', array(
					'presenter' => 'AsseticPackage:Assetic',
				)))
				->setAutowired(FALSE)
				->addTag('route', array('priority' => 100));

		} else {
			$container->addDefinition('assetic_assetStorage')
				->setClass('Kdyby\Assets\Storage\PublicStorage', array(
				$options['publicDir'] . '/' . $options['prefix'], '@httpRequest'
			));
		}

		$container->addDefinition('assetic_filterManager')
			->setClass('Kdyby\Assets\FilterManager', array('@container'));

		$container->addDefinition('assetic_assetManager')
			->setClass('Kdyby\Assets\AssetManager');

		$container->addDefinition('assetic_assetFactory')
			->setClass('Kdyby\Assets\AssetFactory', array(
				'@application_packageManager', '@container', $options['publicDir']
			))
			->addSetup('setAssetManager', array('@assetic_assetManager'))
			->addSetup('setFilterManager', array('@assetic_filterManager'))
			->addSetup('setDefaultOutput', array($options['output'] . '/*'))
			->addSetup('setDebug', array($debug));

		$container->addDefinition('assetic_formulaeManager')
			->setClass('Kdyby\Assets\FormulaeManager', array(
				'@assetic_assetStorage', '@assetic_assetManager', '@assetic_filterManager'
			))
			->addSetup('setDebug', array($debug));

		$container->addDefinition('assetic_assetMacros')
			->setClass('Kdyby\Assets\Latte\AsseticMacroSet')
			->setFactory('Kdyby\Assets\Latte\AsseticMacroSet::install', array('%parser%'))
			->addSetup('setFactory', array('@assetic_assetFactory'))
			->setParameters(array('parser'))
			->addTag('latte_macro');
	}


	// todo: register filters by tags

}
