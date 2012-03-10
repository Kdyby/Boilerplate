<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tools;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @param string $defaultFormat
 */
class DateTime extends Nette\DateTime
{

	/** @var string */
	private $defaultFormat = 'j.n.Y G:i';



	/**
	 * DateTime object factory.
	 * @param  string|int|\DateTime
	 * @return DateTime
	 */
	public static function from($time)
	{
		if ($time instanceof \DateTime) {
			return new static(
					date('Y-m-d H:i:s', $time->getTimestamp()),
					$time->getTimezone()
				);
		}

		return parent::from($time);
	}



	/**
	 * @param array $formats
	 * @param $date
	 */
	public static function tryFormats(array $formats, $date)
	{
		foreach ($formats as $format) {
			if ($valid = static::createFromFormat('!' . $format, $date)) {
				return $valid;
			}
		}

		return FALSE;
	}



	/**
	 * @param string $defaultFormat
	 * @return DateTime
	 */
	public function setDefaultFormat($defaultFormat)
	{
		$this->defaultFormat = (string)$defaultFormat;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getDefaultFormat()
	{
		return $this->defaultFormat;
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->format($this->defaultFormat);
	}

}
