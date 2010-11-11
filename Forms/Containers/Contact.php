<?php

namespace Kdyby\Form\Container;

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
