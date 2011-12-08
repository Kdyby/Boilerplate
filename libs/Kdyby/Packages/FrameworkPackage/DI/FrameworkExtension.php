<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Packages\FrameworkPackage\DI;

use Kdyby;
use Kdyby\DI\Loader\NeonFileLoader;
use Nette\Reflection\ClassType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FrameworkExtension extends Kdyby\DI\Extension
{

	/** @var \Kdyby\DI\Loader\NeonFileLoader */
	private $configLoader;



	/**
	 * @param array $configs
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
    public function load(array $configs, ContainerBuilder $container)
	{
		$this->configLoader = new NeonFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

		$this->loadDefaults($container);
		$this->loadHttp($container);
		$this->loadApplication($container);
		$this->loadConsole($container);
		$this->loadMail($container);
		$this->loadSecurity($container);
		$this->loadTemplate($container);
		$this->loadCache($container);
		$this->loadLoader($container);
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	private function loadDefaults(ContainerBuilder $container)
	{
		$this->configLoader->load('defaults.neon');
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	private function loadHttp(ContainerBuilder $container)
	{
		$this->configLoader->load('http.neon');
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	private function loadApplication(ContainerBuilder $container)
	{
		$this->configLoader->load('application.neon');
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	private function loadConsole(ContainerBuilder $container)
	{
		$this->configLoader->load('console.neon');
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	private function loadMail(ContainerBuilder $container)
	{
		$this->configLoader->load('mail.neon');
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	private function loadSecurity(ContainerBuilder $container)
	{
		$this->configLoader->load('security.neon');
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	private function loadTemplate(ContainerBuilder $container)
	{
		$this->configLoader->load('template.neon');
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	private function loadCache(ContainerBuilder $container)
	{
		$this->configLoader->load('cache.neon');
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	private function loadLoader(ContainerBuilder $container)
	{
		$this->configLoader->load('loader.neon');
	}



    /**
     * Returns the namespace to be used for this extension (XML namespace).
     *
     * @return string The XML namespace
     */
    public function getNamespace()
    {
        return 'http://kdyby.org/schema/dic/framework';
    }

}
