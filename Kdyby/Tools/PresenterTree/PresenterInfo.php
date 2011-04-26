<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Tools\PresenterTree;

use Nette;
use Nette\Reflection\ClassType;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class PresenterInfo extends Nette\Object
{

	/** @var string */
	private $name;

	/** @var string */
	private $module;

	/** @var string */
	private $class;

	/** @var array */
	private $actions;

	/** @var Kdyby\Tools\PresenterTree\PresenterTree */
	private $tree;



	/**
	 * @param string $name
	 * @param string $module
	 * @param string $class
	 */
	public function __construct($name, $module, $class)
	{
		$this->name = $name;
		$this->module = $module;
		$this->class = $class;
	}



	/**
	 * @return bool
	 */
	public function isPublic()
	{
		$ref = $this->getPresenterReflection();
		return !$ref->hasAnnotation('hideInTree');
	}



	/**
	 * @return ClassType
	 */
	public function getPresenterReflection()
	{
		return new ClassType($this->getPresenterClass());
	}



	/**
	 * @return array
	 */
	public function getActions()
	{
		if ($this->actions === NULL) {
			$this->actions = $this->getTree()->getPresenterActions($this);
		}

		return $this->actions;
	}



	/**
	 * @return string
	 */
	public function getName($full = FALSE)
	{
		return ($full ? ':' . $this->module . ':' : NULL) . $this->name;
	}



	/**
	 * @return string
	 */
	public function getPresenterClass()
	{
		return $this->class;
	}



	/**
	 * @return string
	 */
	public function getModule()
	{
		return $this->module;
	}



	public function __toString()
	{
		return $this->getName(TRUE);
	}



	/**
	 * @return Kdyby\Tools\PresenterTree\PresenterTree
	 */
	private function getTree()
	{
		if ($this->tree === NULL) {
			$this->tree = $this->getContext()->getService("Kdyby\\PresenterTree");
		}

		return $this->tree;
	}



	/**
	 * @return Nette\DI\Context
	 */
	private function getContext()
	{
		return Nette\Environment::getApplication()->getContext();
	}



	public function __sleep()
	{
		$properties = (array)$this;
		unset($properties['tree'], $properties['actions']);
		return array_keys($properties);
	}

}
