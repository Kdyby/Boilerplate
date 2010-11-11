<?php

namespace Kdyby\Form\Mapper;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
interface IMapper
{

    public function load($data);
	public function save();

}
