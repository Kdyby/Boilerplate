<?php

namespace Kdyby\DI;

use Kdyby;
use Nette;
use Nette\DI;



/**
 * @author Filip Procházka
 */
class Configurator extends Nette\Configurator
{

	/**
	 * @param string $containerClass
	 */
	public function __construct($containerClass = 'Kdyby\DI\Container')
	{
		parent::__construct($containerClass);
	}



	/**
	 * @param Nette\DI\Container $container
	 * @return Kdyby\Doctrine\Container
	 */
	public static function createServiceDoctrine(DI\Container $container)
	{
		$doctrine = new Kdyby\Doctrine\Container;
		$doctrine->container = $container;

		return $doctrine;
	}



}