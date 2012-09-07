<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Browser;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class DomException extends \Exception
{

	/** @var int */
	private $documentLine;

	/** @var string */
	private $source;



	/**
	 * @param int $line
	 */
	public function setDocumentLine($line)
	{
		$this->documentLine = $line;
	}



	/**
	 * @return int
	 */
	public function getDocumentLine()
	{
		return $this->documentLine;
	}



	/**
	 * @param string $source
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}



	/**
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}

}
