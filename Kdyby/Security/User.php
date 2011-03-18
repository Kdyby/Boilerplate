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


namespace Kdyby\Security;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 *
 * @property-read Kdyby\Templates\Theme $theme
 */
class User extends Nette\Web\User
{

	/** @var Kdyby\Templates\Theme */
	private $theme;



	/**
	 * @return Kdyby\Templates\Theme
	 */
	public function getTheme()
	{
		if ($this->theme === NULL) {
			// temporary
			$this->theme = new Kdyby\Templates\Theme(
					Nette\Environment::expand('%baseUri%'),
					Nette\Environment::expand('%wwwDir%/_default')
				);
		}

		return $this->theme;
	}

}