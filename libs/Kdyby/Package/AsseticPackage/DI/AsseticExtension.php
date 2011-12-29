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
		$container->parameters['assetic_debug'] = $debug = (bool)$container->expand($options['debug']);

		$options['publicDir'] = $debug ? '%tempDir%/public' : $options['publicDir'] . '/' . $options['prefix'];
		$container->parameters['assetic_publicPrefix'] = $options['prefix'];
		$prefix = $options['prefix'] . '/';

		if ($debug) {
			$container->addDefinition('asseticPackage_asseticPresenter')
				->setClass('Kdyby\Package\AsseticPackage\Presenter\AsseticPresenter', array($options['publicDir']))
				->setAutowired(FALSE);

			$container->addDefinition('assetic_route_asset')
				->setClass('Nette\Application\Routers\Route', array("/$prefix<path .*>", array(
					'presenter' => 'AsseticPackage:Assetic',
				)))
				->setAutowired(FALSE)
				->addTag('route', array('priority' => 100));
		}

		$container->addDefinition('assetic_filterManager')
			->setClass('Kdyby\Package\AsseticPackage\FilterManager', array('@container'));

		$container->addDefinition('assetic_assetManager')
			->setClass('Assetic\AssetManager');

		$container->addDefinition('assetic_assetFactory')
			->setClass('Kdyby\Package\AsseticPackage\AssetFactory', array(
				'@application_packageManager', '@container', $options['publicDir'], $debug
			))
			->addSetup('setAssetManager', array('@assetic_assetManager'))
			->addSetup('setFilterManager', array('@assetic_filterManager'));

		$container->addDefinition('assetic_assetWriter')
			->setClass('Kdyby\Package\AsseticPackage\Writer\AssetWriter', array($options['publicDir']));

		$container->addDefinition('assetic_formulaeManager')
			->setClass('Kdyby\Package\AsseticPackage\FormulaeManager', array(
				'@assetic_assetFactory', '@assetic_assetWriter', $prefix, $debug
			));

		$container->addDefinition('assetic_assetMacros')
			->setClass('Kdyby\Package\AsseticPackage\Latte\AsseticMacroSet')
			->setFactory('Kdyby\Package\AsseticPackage\Latte\AsseticMacroSet::install', array('%parser%'))
			->addSetup('setManager', array('@assetic_formulaeManager'))
			->setParameters(array('parser'))
			->addTag('latte_macro');
	}

}
