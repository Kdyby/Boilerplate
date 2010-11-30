<?php

namespace Kdyby\ORM\Mapping;

use Nette;
use Nette\Reflection\ClassReflection;
use Nette\Reflection\PropertyReflection;
use Kdyby;
use ORM;
use ORM\Session;
use ReflectionMethod;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class AnnotationEntityMap extends ORM\Mapping\EntityMap
{

	/** @var array */
	private static $methods = array();



	/**
	 * @param string $entityType
	 * @param ORM\Session $session
	 */
	public function __construct($entityType, Session $session)
	{
		parent::__construct($entityType, $session);

		foreach ($this->getEntityPropertiesAnnotations($entityType) as $name => $annotations) {
			$this->addProperty($name);

			foreach ($annotations as $type => $options) {
				$options = is_array($options) ? current($options) : array();
				$options = $options instanceof \ArrayObject ? $options->getArrayCopy() : $options;

				$method = 'add'.$type;
				if ($this->hasMethod($method)) {
					call_user_func_array(array($this, $method), array('name' => $name) + $options);
					break;
				}
			}
		}
	}



	/**
	 * @param Nette\Reflection\ClassReflection $class
	 * @return array
	 */
	private function getEntityPropertiesAnnotations($class)
	{
		$properties = array();
		$class = new ClassReflection($class);
		foreach ($class->getProperties() as $property) {
			$properties[$property->getName()] = $property->getAnnotations();
		}

		return $properties;
	}



	/**
	 * @param string $method
	 * @return bool
	 */
	private function hasMethod($method)
	{
		$class = get_class($this);

		if (!isset(self::$methods[$class])) {
			$methods = array_map(function($method){
				return $method->name;
			}, $this->reflection->getMethods(ReflectionMethod::IS_PUBLIC));

			self::$methods[$class] = array_filter($methods, function($method){
				return substr($method, 0, 3) === 'add';
			});
		}

		return in_array($method, self::$methods[$class]);
	}

}