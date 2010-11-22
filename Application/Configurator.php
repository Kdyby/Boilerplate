<?php

namespace Kdyby;

use Nette;
use Kdyby;



/**
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



	/**
	 * @return Nette\Web\IUser
	 */
	public static function createIUser()
	{
//		$dtm = Nette\Environment::getService("Kdyby\\Database\\DtM");

		return $user = new Kdyby\Security\User;
	}



	/**
	 * @return Nette\Caching\ICacheStorage
	 */
	public static function createCacheStorage()
	{
		$context = new Nette\Context;
		$context->addService('Nette\\Caching\\ICacheJournal', callback('Nette\Configurator::createCacheJournal'));

		$dir = FileService::prepareDir(Nette\Environment::expand('%tempDir%/cache'));

		return new Nette\Caching\FileStorage($dir, $context);
	}

}