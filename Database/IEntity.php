<?php

namespace Kdyby\Database;

use Nette;
use Kdyby;



/**
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
interface IEntity
{

	public function getRepository();

	public function save();

}
