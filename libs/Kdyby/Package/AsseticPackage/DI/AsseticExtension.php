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

	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param array $config
	 */
	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		if (!isset($config['read_from'])) {
			$config['read_from'] = '%wwwDir%';
		}
		if (!isset($config['write_to'])) {
			$config['write_to'] = '%read_from%';
		}

		$container->addDefinition('assetic_filterManager')
			->setClass('Kdyby\Package\AsseticPackage\FilterManager', array('@container'));

		$container->addDefinition('assetic_assetManager')
			->setClass('Assetic\AssetManager');

		$container->addDefinition('assetic_assetFactory')
			->setClass('Kdyby\Package\AsseticPackage\AssetFactory', array(
				'@application_packageManager', '@container', $config['read_from'], '%kdyby_debug%'
			))
			->addSetup('setAssetManager', array('@assetic_assetManager'))
			->addSetup('setFilterManager', array('@assetic_filterManager'));

		$container->addDefinition('assetic_assetWriter')
			->setClass('Kdyby\Package\AsseticPackage\Writer\AssetWriter', array($config['write_to']));

		$container->addDefinition('assetic_formulaeManager')
			->setClass('Kdyby\Package\AsseticPackage\FormulaeManager', array(
				'@assetic_assetFactory', '@assetic_assetWriter'
			));

	}

}
