<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Kdyby;
use Nette;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;



/**
 * Provides useful features shared by many extensions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class Extension extends Nette\Object implements ExtensionInterface
{

	/**
	 * Returns the base path for the XSD files.
	 *
	 * @return string
	 */
	public function getXsdValidationBasePath()
	{
		return FALSE;
	}



	/**
	 * Returns the namespace to be used for this extension (XML namespace).
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		return 'http://example.org/schema/dic/' . $this->getAlias();
	}



	/**
	 * Returns the recommended alias to use in XML.
	 *
	 * This alias is also the mandatory prefix to use when using YAML.
	 *
	 * This convention is to remove the "Extension" postfix from the class
	 * name and then lowercase and underscore the result. So:
	 *
	 *     AcmeHelloExtension
	 *
	 * becomes
	 *
	 *     acme_hello
	 *
	 * This can be overridden in a sub-class to specify the alias manually.
	 *
	 * @return string
	 */
	public function getAlias()
	{
		$className = get_class($this);
		if (substr($className, -9) != 'Extension') {
			throw new Nette\InvalidStateException('This extension does not follow the naming convention; you must overwrite the getAlias() method.');
		}
		$classBaseName = substr(strrchr($className, '\\'), 1, -9);

		return Container::underscore($classBaseName);
	}



	/**
	 * @param ConfigurationInterface $configuration
	 * @param array $configs
	 * @return array
	 */
	protected final function processConfiguration(ConfigurationInterface $configuration, array $configs)
	{
		$processor = new Processor();
		return $processor->processConfiguration($configuration, $configs);
	}

}
