<?php

namespace Kdyby;

use Nette;



/**
 * @author Filip Procházka
 * @static
 */
final class Framework
{

	const NAME = 'Kdyby Framework';
	const VERSION = '8.1-dev';
	const REVISION = '$WCREV$ released on $WCDATE$';



	/**
	 * @throws Nette\StaticClassException
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}

}