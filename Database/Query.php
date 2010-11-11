<?php

namespace Kdyby\Database;

use Nette;
use Kdyby;



/**
 * Description of Query
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Query extends Nette\Object
{

	public $conditions = array();
	

	public function __set($property, $value)
	{
		$this->conditions[$property] = $value;
	}

}