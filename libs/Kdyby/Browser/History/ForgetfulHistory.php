<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Browser\History;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ForgetfulHistory extends EagerHistory
{

	/**
	 * @param \Kdyby\Browser\WebPage|\stdClass $content
	 * @param \Kdyby\Curl\Request|null $request
	 * @param \Kdyby\Curl\Response|null $response
	 */
	public function push($content, Kdyby\Curl\Request $request = NULL, Kdyby\Curl\Response $response = NULL)
	{
		$this->clean();
		parent::push($content, $request, $response);
	}

}
