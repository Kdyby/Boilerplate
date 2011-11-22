<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface IDependentPackage
{

	/**
	 * Returns list of dependencies
	 *
	 * @return array
	 */
	function getDependencies();

}