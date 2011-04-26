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
 * Description of Address
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Address extends Nette\Forms\Container
{

	public function attached($presenter)
	{
		parent::attached($presenter);

		$this->addSelect('state', 'State', array(
			'czech-republic' => "Česká republika",
			'slovakia' => "Slovensko",
			'deutschland' => "Německo",
			'great-britain' => "Velká Británie",
		));

		$this->addSelect('city', 'City', array(
			"Praha",
			"London",
			"Berlin",
			"Brno",
			"Bratislava"
		));

		$this->addSelect('street', 'Street', array(
			"blibla",
			"blendabla",
			"blatník",
			"blatouch",
			"ropucha"
		));

		$this->addText('number', "Number");

		$this->addText('orientationNumber', 'Orientation number');
	}

}
