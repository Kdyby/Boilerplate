<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Browser;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
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
