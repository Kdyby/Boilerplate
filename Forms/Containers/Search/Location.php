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