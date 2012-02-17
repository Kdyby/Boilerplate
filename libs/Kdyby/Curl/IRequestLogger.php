<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Curl;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface IRequestLogger
{

	/**
	 * @param \Kdyby\Curl\Request $request
	 * @return string the id to pass to response
	 */
	function request(Request $request);


	/**
	 * @param \Kdyby\Curl\Response $response
	 * @param string $id
	 */
	function response(Response $response, $id);

}
