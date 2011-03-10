<?php

namespace KdybyTests\Injection\Mocks;

use Kdyby;
use Nette;



class ServiceMock
{

	public $propertyConstructor = array();

	public $calledMethod = array();

	public $propertyGet = array();

	public $propertySet = array();



	public function __construct()
	{
		$this->propertyConstructor = func_get_args();
	}



	public function __call($name, $args)
	{
		$this->calledMethod[] = array($name, $args);
	}



	public function __get($name)
	{
		$this->propertyGet[] = $name;
	}



	public function __set($name, $args)
	{
		$this->propertySet[] = array($name, $args);
	}



	public static function createServiceMock($options)
	{
		return new self(&$options['entityDir'], &$options['proxyDir']);
	}


}