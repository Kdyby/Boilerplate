<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Browser\History;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ForgetfulHistory extends EagerHistory
{

	/**
	 * @param \Kdyby\Extension\Browser\WebPage|\stdClass $content
	 * @param \Kdyby\Extension\Curl\Request|null $request
	 * @param \Kdyby\Extension\Curl\Response|null $response
	 */
	public function push($content, Kdyby\Extension\Curl\Request $request = NULL, Kdyby\Extension\Curl\Response $response = NULL)
	{
		$this->clean();
		parent::push($content, $request, $response);
	}

}
