<?php

namespace Kdyby;

use Nette;
use Kdyby;



/**
 * Description of Configurator
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Configurator extends Nette\Object
{

	public static function createDtM()
	{
		$configurator = new Database\DtMConfigurator;

		$dtm = new Database\DtM($configurator);

		return $dtm;
	}


	public static function createIUser()
	{
		$dtm = Nette\Environment::getService("Kdyby\\Database\\DtM");

		return $user = new Kdyby\Entity\User($dtm->getRepository('users'));
	}

}
