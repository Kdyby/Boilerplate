<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms\Controls;

use Kdyby;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class DateTimeBase extends Nette\Forms\Controls\BaseControl
{

	/** @var \Datetime */
	protected $dateTime;

	/** @var bool */
	private $valid = TRUE;



	/**
	 * @param string $caption
	 */
	public function __construct($caption = NULL)
	{
		parent::__construct($caption);
		$this->control->size = 10;
		$this->control->maxlength = 10;
	}



	/**
	 * @param string|int|\DateTime $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
		if (!$value) {
			return;
		}

		try {
			$this->dateTime = Nette\DateTime::from($value);

		} catch (\Exception $e) {
			$this->valid = FALSE;
		}
	}



	/**
	 * @return string
	 */
	abstract public function getFormat();



	/**
	 * @return string|NULL
	 */
	public function getValue()
	{
		return ($this->value && $this->dateTime) ? $this->dateTime->format($this->getFormat()) : NULL;
	}



	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		parent::loadHttpData();
		$this->dateTime = Nette\DateTime::createFromFormat('!' . $this->getFormat(), $this->value);
		$this->valid = (bool)($this->dateTime);
	}



	/**
	 * @return \Nette\Utils\Html
	 */
	public function getControl()
	{
		/** @var \stdClass|\Nette\Utils\Html $control */
		$control = parent::getControl();
		$control->value = $this->dateTime ? $this->dateTime->format($this->getFormat()) : $this->value;

		// mask for typing dates
		$mask = Strings::replace(date_create()->format($this->getFormat()), array('~[a-z]~i' => 's', '~[0-9]~' => 'd'));
		$control->data('kdyby-input-mask', $mask); // todo
		$control->data('kdyby-datetime-format', $this->getFormat());

		return $control;
	}



	/**
	 * @return bool
	 */
	public function isValid()
	{
		return $this->valid;
	}



	/**
	 * @param \Kdyby\Forms\Controls\DateTimeBase|int|string $time1
	 * @param \Kdyby\Forms\Controls\DateTimeBase|int|string $time2
	 * @param string|\DateInterval $modify
	 *
	 * @return string|NULL
	 */
	protected static function getDiff($time1, $time2, $modify = NULL)
	{
		if (!($time1 = static::datetimeFrom($time1)) || !($time2 = static::datetimeFrom($time2))) {
			return NULL;
		}

		if ($modify !== NULL) {
			$time2->modify($modify);
		}

		$time1->setTime(0, 0, 0);
		$time2->setTime(0, 0, 0);

		/** @var \DateInterval $diff */
		$diff = $time1->diff($time2);
		return $diff->format('%r%a');
	}



	/**
	 * @param \Kdyby\Forms\Controls\DateTimeBase|int|string $time
	 *
	 * @return \DateTime|NULL
	 */
	private static function datetimeFrom($time)
	{
		if ($time instanceof self) {
			$time = $time->dateTime;
			$time = $time ? clone $time : FALSE;

		} else {
			$time = Nette\DateTime::from($time);
		}

		return $time ? : NULL;
	}



	/**
	 * @param \Kdyby\Forms\Controls\DateTimeBase $time1
	 * @param \DateTime|int|string $time2
	 * @param string|\DateInterval $modify
	 *
	 * @return bool
	 */
	public static function validateMin(DateTimeBase $time1, $time2, $modify = NULL)
	{
		if (is_array($time2)) {
			list($time2, $modify) = $time2;
		}

		return ($diff = self::getDiff($time1, $time2, $modify)) === FALSE ? : $diff < 0;
	}



	/**
	 * @param \Kdyby\Forms\Controls\DateTimeBase $time1
	 * @param \DateTime|int|string $time2
	 * @param string|\DateInterval $modify
	 *
	 * @return bool
	 */
	public static function validateMinOrEqual(DateTimeBase $time1, $time2, $modify = NULL)
	{
		if (is_array($time2)) {
			list($time2, $modify) = $time2;
		}

		return ($diff = self::getDiff($time1, $time2, $modify)) === FALSE ? : $diff <= 0;
	}



	/**
	 * @param \Kdyby\Forms\Controls\DateTimeBase $time1
	 * @param \DateTime|int|string $time2
	 * @param string|\DateInterval $modify
	 *
	 * @return bool
	 */
	public static function validateMaxOrEqual(DateTimeBase $time1, $time2, $modify = NULL)
	{
		if (is_array($time2)) {
			list($time2, $modify) = $time2;
		}

		return ($diff = self::getDiff($time1, $time2, $modify)) === FALSE ? : $diff >= 0;
	}



	/**
	 * @param \Kdyby\Forms\Controls\DateTimeBase $time1
	 * @param \DateTime|int|string $time2
	 * @param string|\DateInterval $modify
	 *
	 * @return bool
	 */
	public static function validateMax(DateTimeBase $time1, $time2, $modify = NULL)
	{
		if (is_array($time2)) {
			list($time2, $modify) = $time2;
		}

		return ($diff = self::getDiff($time1, $time2, $modify)) === FALSE ? : $diff > 0;
	}



	/**
	 * @param \Kdyby\Forms\Controls\DateTimeBase $control
	 *
	 * @return bool
	 */
	public static function validateValidDate(DateTimeBase $control)
	{
		return $control->isValid();
	}



	/**
	 * @param \Nette\Forms\Controls\BaseControl|\Nette\Forms\IControl $control
	 *
	 * @return bool
	 */
	public static function validateFilled(Nette\Forms\IControl $control)
	{
		return (string)$control->value !== ''; // NULL, FALSE, '' ==> FALSE
	}

}
