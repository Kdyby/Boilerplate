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
		'read_from' => '%wwwDir%',
		'write_to' => '%tempDir%/public',
		'debug' => '%kdyby_debug%'
	);



	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param array $config
	 */
	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		$options = self::getOptions($config, $this->asseticDefaults);
		$debug = (bool)$container->expand($options['debug']);
		$container->parameters['assetic_debug'] = $debug;

		if ($debug) {
			$options['write_to'] = '%tempDir%/public';
		}

		$container->addDefinition('assetic_filterManager')
			->setClass('Kdyby\Package\AsseticPackage\FilterManager', array('@container'));

		$container->addDefinition('assetic_assetManager')
			->setClass('Assetic\AssetManager');

		$container->addDefinition('assetic_assetFactory')
			->setClass('Kdyby\Package\AsseticPackage\AssetFactory', array(
				'@application_packageManager', '@container', $options['read_from'], $debug
			))
			->addSetup('setAssetManager', array('@assetic_assetManager'))
			->addSetup('setFilterManager', array('@assetic_filterManager'));

		$container->addDefinition('assetic_assetWriter')
			->setClass('Kdyby\Package\AsseticPackage\Writer\AssetWriter', array($options['write_to']));

		$container->addDefinition('assetic_formulaeManager')
			->setClass('Kdyby\Package\AsseticPackage\FormulaeManager', array(
				'@assetic_assetFactory', '@assetic_assetWriter', $debug
			));

		$container->addDefinition('asseticPackage_asseticPresenter')
			->setClass('Kdyby\Package\AsseticPackage\Presenter\AsseticPresenter', array($options['read_from']))
			->setAutowired(FALSE);
	}

}
