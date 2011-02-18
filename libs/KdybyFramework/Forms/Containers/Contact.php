<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Forms\Container;

use Nette;
use Kdyby;



/**
 * Description of Contact
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Contact extends Nette\Forms\FormContainer
{

	public function attached($presenter)
	{
		parent::attached($presenter);

		$this->addText('phones', 'Phones')
			->setOption('description', "Separate each phone number with comma");

		$this->addText('fax', 'Fax');

		$this->addText('email', 'Email');

		$this['address'] = new Address;
	}

}
