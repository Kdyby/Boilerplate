<?php

namespace Kdyby\Entity;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Currency extends Nette\Object
{

	/** @var int */
	private $id;

	/** @var string */
	public $name;


	public function getId()
	{
		return $this->id;
	}
}