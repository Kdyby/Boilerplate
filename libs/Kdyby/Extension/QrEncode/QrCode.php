<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\QrEncode;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 * @see http://fukuchi.org/works/qrencode/index.html.en
 */
class QrCode extends QrOptions
{
	const STRUCTURED = 1; // --structured
	const KANJI = 2; // --kanji
	const CASE_SENSITIVE = 4; // --casesensitive
	const CASE_INSENSITIVE = 8; // --ignorecase
	const ENCODE_8BIT = 16; // --8bit

	const ERR_CORR_L = 'L';
	const ERR_CORR_M = 'M';
	const ERR_CORR_Q = 'Q';
	const ERR_CORR_H = 'H';

	/**
	 * @var string
	 */
	private $string;



	/**
	 * @param string $string
	 * @param int $size the size of dot (pixel). (default=3)
	 * @param string $level error collection level from L (lowest) to H (highest). (default=L)
	 * @param int $version the version of the symbol. (default=auto)
	 * @param int $margin the width of margin. (default=4)
	 * @param int $options
	 */
	public function __construct($string, $size = NULL, $level = NULL, $version = NULL, $margin = NULL, $options = 0)
	{
		parent::__construct($size, $level, $version, $margin, $options);
		$this->string = $string;
	}



	/**
	 * @return string
	 */
	public function getString()
	{
		return $this->string;
	}



	/**
	 * @param QrGenerator $generator
	 */
	public function render(QrGenerator $generator = NULL)
	{
		$generator = $generator ?: new QrGenerator();
		return $generator->render($this);
	}



	/**
	 * @param string $file
	 * @param QrGenerator $generator
	 * @throws IOException
	 */
	public function save($file, QrGenerator $generator = NULL)
	{
		if (!@file_put_contents($file, $this->render($generator))) {
			throw new IOException("Cannot write to $file.");
		}
	}

}
