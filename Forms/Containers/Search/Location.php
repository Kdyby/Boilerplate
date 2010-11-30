<?php

namespace Kdyby\Form\Container;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class LocationSearch extends Nette\Forms\FormContainer
{

	public function attached($presenter)
	{
		parent::attached($presenter);

		$this->addSelect('state', "Stát", array(
			'czech-republic' => "Česká republika",
			'slovakia' => "Slovensko",
			'deutschland' => "Německo",
			'great-britain' => "Velká Británie",
		));

		//$this->addCheckboxList('');
	}

}