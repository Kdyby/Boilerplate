<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class UIFormStub extends Nette\Application\UI\Form
{

	/**
	 * @var array
	 */
	private $fakeHttpValues;



	/**
	 * @param array $values
	 */
	public function __construct($values = array())
	{
		parent::__construct();
		$this->fakeHttpValues = $values;
	}



	/**
	 * @return bool
	 */
	public function isAnchored()
	{
		return TRUE;
	}



	/**
	 * @return array
	 */
	protected function receiveHttpData()
	{
		return $this->fakeHttpValues;
	}

}
