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
 */
abstract class QrOptions extends Nette\Object
{

	/**
	 * The size of dot (pixel). (default=3)
	 *
	 * @var int
	 */
	private $size;

	/**
	 * Error collection level from L (lowest) to H (highest). (default=L)
	 *
	 * @var null
	 */
	private $errorCorrection;

	/**
	 * The version of the symbol. (default=auto)
	 *
	 * @var int
	 */
	private $version;

	/**
	 * The width of margin. (default=4)
	 *
	 * @var int
	 */
	private $margin;

	/**
	 * @var int
	 */
	private $options = 0;



	/**
	 * Accepts default settings.
	 *
	 * @param int $size the size of dot (pixel). (default=3)
	 * @param string $level error collection level from L (lowest) to H (highest). (default=L)
	 * @param int $version the version of the symbol. (default=auto)
	 * @param int $margin the width of margin. (default=4)
	 * @param int $options
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct($size = NULL, $level = NULL, $version = NULL, $margin = NULL, $options = 0)
	{
		if ($level !== NULL) {
			$errCorrConst = __NAMESPACE__ . '\QrCode::ERR_CORR_' . strtoupper($level);
			if (!defined($errCorrConst)) {
				throw new InvalidArgumentException("Unknown error correction level $level.");
			}

			$this->errorCorrection = constant($errCorrConst);
		}

		$this->size = $size;
		$this->version = $version;
		$this->margin = $margin;
		$this->options = $options;
	}



	/**
	 * @param string $default
	 * @return null
	 */
	public function getErrorCorrection($default = NULL)
	{
		return $this->errorCorrection ?: $default;
	}



	/**
	 * @param int $default
	 * @return int
	 */
	public function getMargin($default = NULL)
	{
		return $this->margin ?: $default;
	}



	/**
	 * @param int $default
	 * @return int
	 */
	public function getOptions($default = NULL)
	{
		return $this->options ?: $default;
	}



	/**
	 * @param int $option
	 * @param int $default
	 * @return bool
	 */
	public function hasOption($option, $default = NULL)
	{
		$value = (int)($this->options ? : $default);
		return (bool)($value & $option);
	}



	/**
	 * @param int $default
	 * @return int
	 */
	public function getSize($default = NULL)
	{
		return $this->size ?: $default;
	}



	/**
	 * @param int $default
	 * @return int
	 */
	public function getVersion($default = NULL)
	{
		return $this->version ?: $default;
	}

}
