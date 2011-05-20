<?php

namespace Kdyby\DI;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class Configurator extends Nette\Configurator
{


	/**
	 * @param Nette\DI\Container $container
	 * @return Kdyby\Doctrine\Container
	 */
	public static function createServiceDoctrine(Nette\DI\Container $container)
	{
		$doctrine = new Kdyby\Doctrine\Container;
		$doctrine->container = $container;

		return $doctrine;
	}

}