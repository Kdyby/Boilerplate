<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI\Loader;

use Kdyby;
use Kdyby\DI\FileLoaderImportLogger;
use Nette;
use Nette\Utils\Neon;
use Symfony;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Resource\FileResource;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class NeonFileLoader extends Symfony\Component\DependencyInjection\Loader\FileLoader
{

	/** @var \Kdyby\DI\FileLoaderImportLogger */
	private $logger;



	/**
	 * @param \Kdyby\DI\FileLoaderImportLogger $logger
	 */
	public function setLogger(FileLoaderImportLogger $logger)
	{
		$this->logger = $logger;
	}



	/**
	 * Imports a resource.
	 *
	 * @param mixed $resource
	 * @param string $type
	 * @param bool $ignoreErrors
	 * @param string $sourceResource
	 *
	 * @return mixed
	 */
	public function import($resource, $type = NULL, $ignoreErrors = FALSE, $sourceResource = NULL)
	{
		if ($this->logger !== NULL) {
			$this->logger->log($resource, $type, $ignoreErrors, $sourceResource);
		}

		return parent::import($resource, $type, $ignoreErrors, $sourceResource);
	}



	/**
	 * Loads a Neon file.
	 *
	 * @param mixed $file
	 * @param string $type
	 */
	public function load($file, $type = NULL)
	{
		$path = $this->locator->locate($file);

		$content = $this->loadFile($path);

		$this->container->addResource(new FileResource($path));

		// empty file
		if (NULL === $content) {
			return;
		}

		// imports
		$this->parseImports($content, $file);

		// parameters
		if (isset($content['parameters'])) {
			foreach ($content['parameters'] as $key => $value) {
				$this->container->setParameter($key, $this->resolveServices($value));
			}
		}

		// extensions
		$this->loadFromExtensions($content);

		// services
		$this->parseDefinitions($content, $file);
	}



	/**
	 * Returns TRUE if this class supports the given resource.
	 *
	 * @param mixed $resource
	 * @param string $type
	 *
	 * @return bool TRUE if this class supports the given resource, FALSE otherwise
	 */
	public function supports($resource, $type = NULL)
	{
		return is_string($resource) && 'neon' === pathinfo($resource, PATHINFO_EXTENSION);
	}



	/**
	 * Parses all imports
	 *
	 * @param array $content
	 * @param string $file
	 *
	 * @return void
	 */
	private function parseImports($content, $file)
	{
		if (!isset($content['imports'])) {
			return;
		}

		foreach ($content['imports'] as $import) {
			$this->setCurrentDir(dirname($file));
			$this->import($import['resource'], NULL, isset($import['ignore_errors']) ? (bool)$import['ignore_errors'] : FALSE, $file);
		}
	}



	/**
	 * Parses definitions
	 *
	 * @param array $content
	 * @param string $file
	 *
	 * @return void
	 */
	private function parseDefinitions($content, $file)
	{
		if (!isset($content['services'])) {
			return;
		}

		foreach ($content['services'] as $id => $service) {
			$this->parseDefinition($id, $service, $file);
		}
	}



	/**
	 * Parses a definition.
	 *
	 * @param string $id
	 * @param array $service
	 * @param string $file
	 *
	 * @return void
	 */
	private function parseDefinition($id, $service, $file)
	{
		if (is_string($service) && 0 === strpos($service, '@')) {
			$this->container->setAlias($id, substr($service, 1));

			return;
		} else {
			if (isset($service['alias'])) {
				$public = !array_key_exists('public', $service) || (bool)$service['public'];
				$this->container->setAlias($id, new Alias($service['alias'], $public));

				return;
			}
		}

		if (isset($service['parent'])) {
			$definition = new DefinitionDecorator($service['parent']);
		} else {
			$definition = new Definition();
		}

		if (isset($service['class'])) {
			$definition->setClass($service['class']);
		}

		if (isset($service['scope'])) {
			$definition->setScope($service['scope']);
		}

		if (isset($service['synthetic'])) {
			$definition->setSynthetic($service['synthetic']);
		}

		if (isset($service['public'])) {
			$definition->setPublic($service['public']);
		}

		if (isset($service['abstract'])) {
			$definition->setAbstract($service['abstract']);
		}

		if (isset($service['factory_class'])) {
			$definition->setFactoryClass($service['factory_class']);
		}

		if (isset($service['factory_method'])) {
			$definition->setFactoryMethod($service['factory_method']);
		}

		if (isset($service['factory_service'])) {
			$definition->setFactoryService($service['factory_service']);
		}

		if (isset($service['file'])) {
			$definition->setFile($service['file']);
		}

		if (isset($service['arguments'])) {
			$definition->setArguments($this->resolveServices($service['arguments']));
		}

		if (isset($service['properties'])) {
			$definition->setProperties($this->resolveServices($service['properties']));
		}

		if (isset($service['configurator'])) {
			if (is_string($service['configurator'])) {
				$definition->setConfigurator($service['configurator']);
			} else {
				$definition->setConfigurator(array($this->resolveServices($service['configurator'][0]), $service['configurator'][1]));
			}
		}

		if (isset($service['calls'])) {
			foreach ($service['calls'] as $call) {
				$definition->addMethodCall($call[0], $this->resolveServices($call[1]));
			}
		}

		if (isset($service['tags'])) {
			if (!is_array($service['tags'])) {
				throw new \InvalidArgumentException(sprintf('Parameter "tags" must be an array for service "%s" in %s.', $id, $file));
			}

			foreach ($service['tags'] as $tag) {
				if (!isset($tag['name'])) {
					throw new \InvalidArgumentException(sprintf('A "tags" entry is missing a "name" key for service "%s" in %s.', $id, $file));
				}

				$name = $tag['name'];
				unset($tag['name']);

				$definition->addTag($name, $tag);
			}
		}

		$this->container->setDefinition($id, $definition);
	}



	/**
	 * Loads a YAML file.
	 *
	 * @param string $file
	 *
	 * @return array The file content
	 */
	private function loadFile($file)
	{
		return $this->validate(Neon::decode(file_get_contents($file)), $file);
	}



	/**
	 * Validates a YAML file.
	 *
	 * @param mixed $content
	 * @param string $file
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException When service file is not valid
	 */
	private function validate($content, $file)
	{
		if (NULL === $content) {
			return $content;
		}

		foreach (array_keys($content) as $namespace) {
			if (in_array($namespace, array('imports', 'parameters', 'services'))) {
				continue;
			}

			if (!$this->container->hasExtension($namespace)) {
				$extensionNamespaces = array_filter(array_map(function ($ext)
				{
					return $ext->getAlias();
				}, $this->container->getExtensions()));
				throw new \InvalidArgumentException(sprintf(
					'There is no extension able to load the configuration for "%s" (in %s). Looked for namespace "%s", found %s',
					$namespace,
					$file,
					$namespace,
					$extensionNamespaces ? sprintf('"%s"', implode('", "', $extensionNamespaces)) : 'none'
				));
			}
		}

		return $content;
	}



	/**
	 * Resolves services.
	 *
	 * @param string $value
	 *
	 * @return \Symfony\Component\DependencyInjection\Reference
	 */
	private function resolveServices($value)
	{
		if (is_array($value)) {
			$value = array_map(array($this, 'resolveServices'), $value);
		} else if (is_string($value) && 0 === strpos($value, '@')) {
			if (0 === strpos($value, '@?')) {
				$value = substr($value, 2);
				$invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
			} else {
				$value = substr($value, 1);
				$invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
			}

			if ('=' === substr($value, -1)) {
				$value = substr($value, 0, -1);
				$strict = FALSE;
			} else {
				$strict = TRUE;
			}

			$value = new Reference($value, $invalidBehavior, $strict);
		}

		return $value;
	}



	/**
	 * Loads from Extensions
	 *
	 * @param array $content
	 *
	 * @return void
	 */
	private function loadFromExtensions($content)
	{
		foreach ($content as $namespace => $values) {
			if (in_array($namespace, array('imports', 'parameters', 'services'))) {
				continue;
			}

			if (!is_array($values)) {
				$values = array();
			}

			$this->container->loadFromExtension($namespace, $values);
		}
	}

}
