<?php

namespace Kdyby\Database;

use dibi;
use Nette;
use Kdyby;



/**
 * Description of ConnectedObject
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class ConnectedObject extends Nette\Object
{

	/**
	 * @return \DibiConnection
	 */
	public function getConnection()
	{
		return dibi::getConnection();
	}

}