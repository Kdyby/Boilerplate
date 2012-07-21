<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\DicFactory;

use Kdyby;
use Nette;
use Nette\DI\ServiceDefinition;
use Nette\Caching\Cache;
use Nette\Caching\Storages\PhpFileStorage;
use Nette\Utils\PhpGenerator as Code;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FactoryGeneratorExtension extends Nette\Config\CompilerExtension
{

	/**
	 * @var string
	 */
	private $classesFile;



	/**
	 * Generates all the factory classes for non-shared services
	 */
	public function beforeCompile()
	{
		$interfaces = $code = array();
		$builder = $this->getContainerBuilder();

		/** @var \Nette\DI\ServiceDefinition $def */
		foreach ($builder->getDefinitions() as $name => $def) {
			$factoryName = $name . 'Factory';
			if ($def->shared || $def->internal) {
				continue;
			}

			if ($builder->hasDefinition($factoryName)) {
				if ($builder->getDefinition($factoryName)->class === 'Nette\Callback') {
					$builder->removeDefinition($factoryName);

				} else {
					continue;
				}
			}

			/** @var \Nette\Utils\PhpGenerator\ClassType $class */
			/** @var \Nette\Utils\PhpGenerator\ClassType $interface */
			list($class, $interface) = $this->createServiceFactory($def, $name);

			// only if class can be resolved
			if ($class === NULL) {
				continue;
			}
			$code[] = $class;

			// interfaces must be unique
			if (!in_array($interface->name, $interfaces, TRUE)) {
				$code[] = $interface;
				$interfaces[] = $interface->name;
			}

			$builder->addDefinition($factoryName)
				->setClass($interface->name)
				->setFactory($class->name)
				->tags = $def->tags;
		}

		// generate and save
		$cache = $this->getCache();
		$cache->save($interfaces, $this->generateCode($this->namespaceClasses($code)));

		// find the file
		$cached = $cache->load($interfaces);
		$this->classesFile = $cached['file'];
		Nette\Utils\LimitedScope::load($this->classesFile, TRUE);

		// add as dependency
		$this->getContainerBuilder()->addDependency($this->classesFile);
	}



	/**
	 * @param \Nette\Utils\PhpGenerator\ClassType $class
	 */
	public function afterCompile(Nette\Utils\PhpGenerator\ClassType $class)
	{
		/** @var \Nette\Utils\PhpGenerator\Method $init */
		$init = $class->methods['initialize'];
		$init->addBody('include_once ?;', array($this->classesFile));
	}



	/**
	 * @param \Nette\DI\ServiceDefinition $def
	 * @param string $serviceName
	 *
	 * @return array
	 */
	private function createServiceFactory(ServiceDefinition $def, $serviceName)
	{
		$builder = $this->getContainerBuilder();

		if ($def->class) {
			$className = $builder->expand($def->class);

		} elseif ($def->factory) {
			$className = $builder->expand($def->factory->entity); // todo

		} else {
			return array(NULL, NULL);
		}

		// naming
		$factoryName = $className . 'Factory';
		$interfaceName = substr_replace($factoryName, 'I', strrpos($factoryName, '\\') + 1, 0);
		$factoryName .= '_' . Strings::random(5);

		// interface
		$interface = new Code\ClassType($interfaceName);
		$interface->setType('interface');
		$this->generateCreateMethod($className, $def, $interface);
		$interface->addDocument('Creates instance of ' . $className);

		// class
		$class = new Code\ClassType($factoryName);
		$class->setFinal(TRUE);
		$class->addExtend('\Nette\Object');
		$class->addImplement('\\' . $interface->name);
		$class->addDocument('@internal');

		// pass in the container
		$class->addProperty('container');
		$constructImpl = $class->addMethod('__construct');
		$containerParam = $constructImpl->addParameter('container');
		$containerParam->setTypeHint('\Nette\DI\Container');
		$constructImpl->addBody('$this->container = $container;');

		// factory implementation
		$createImpl = $this->generateCreateMethod($className, $def, $class);
		$createImpl->setVisibility('public');
		$createImpl->addBody('return callback($this->container, ?)->invokeArgs(func_get_args());', array(
			Nette\DI\Container::getMethodName($serviceName, FALSE),
		));

		// invocation of factory
		$class->methods['__invoke'] = $invokeImpl = clone $createImpl;
		$invokeImpl->name = '__invoke';

		return array($class, $interface);
	}



	/**
	 * @param string $returnType
	 * @param \Nette\DI\ServiceDefinition $def
	 * @param \Nette\Utils\PhpGenerator\ClassType $type
	 *
	 * @return \Nette\Utils\PhpGenerator\Method
	 */
	private static function generateCreateMethod($returnType, ServiceDefinition $def, Code\ClassType $type)
	{
		$create = $type->addMethod('create');
		foreach ($def->parameters as $k => $v) {
			$tmp = explode(' ', is_int($k) ? $v : $k);
			$paramName = end($tmp);
			$defaultValue = is_int($k) ? NULL : $v;

			$create->addParameter($paramName, $defaultValue);
			$create->addDocument('@param $' . $paramName);
		}

		$create->addDocument('@return \\' . $returnType);

		return $create;
	}



	/**
	 * @param array|\Nette\Utils\PhpGenerator\ClassType $classes
	 *
	 * @return \Nette\Utils\PhpGenerator\ClassType
	 */
	private static function namespaceClasses(array $classes)
	{
		$namespaced = array();
		foreach ($classes as $type) {
			$namespace = NULL;

			/** @var \Nette\Utils\PhpGenerator\ClassType $type */
			if (($pos = strrpos($type->name, '\\')) !== FALSE) {
				$namespace = substr($type->name, 0, $pos);
				$type->setName(substr($type->name, $pos + 1));
			}

			$namespaced[$namespace][] = $type;
		}

		return $namespaced;
	}



	/**
	 * @param array|\Nette\Utils\PhpGenerator\ClassType $namespaced
	 * @return string
	 */
	private static function generateCode(array $namespaced)
	{
		$code = array();
		foreach ($namespaced as $namespace => $classes) {
			$code[] = 'namespace ' . $namespace . ' {';
			$code = array_merge($code, $classes);
			$code[] = '}';
		}

		return "<?php\n\n" . implode("\n\n\n", $code);
	}



	/**
	 * @return \Nette\Caching\Cache
	 */
	private function getCache()
	{
		$cacheDir = $this->getContainerBuilder()->expand('%tempDir%/cache');
		return new Cache(new PhpFileStorage($cacheDir), 'Nette.DicFactory');
	}

}
