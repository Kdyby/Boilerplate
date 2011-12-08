<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Packages\FrameworkPackage;

use Kdyby;
use Nette;
use Symfony\Component\DependencyInjection\ContainerBuilder;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FrameworkPackage extends Kdyby\Package\Package
{

	/**
	 * Occurs before the application loads presenter
	 */
	public function onStartup()
	{
		$session = $this->container->get('http.session');
		if ($session->exists()) {
			$session->start();
		}
	}

}
