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



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DateTime extends Nette\Forms\Controls\BaseControl
{

	/** @var string */
	public $format = 'j.n.Y';

	/** @var \DateTime */
	protected $dateTime;

	/** @var boolean */
	private $valid = TRUE;



	/**
	 * @param string $caption
	 */
	public function __construct($caption = NULL)
	{
		parent::__construct($caption);
		$this->control->type = 'date';
		$this->control->size = 10;
		$this->control->maxlength = 10;
	}



	/**
	 * @param string|\DateTime $value
	 * @return void
	 */
	public function setValue($value)
	{
		$this->value = $value;
		if (!$value) return;

		try {
			$this->dateTime = Nette\DateTime::from($value);
			$this->dateTime->setTime(0, 0, 0);

		} catch (\Exception $e) {
			$this->valid = FALSE;
		}
	}



	/**
	 * @return \DateTime|NULL
	 */
	public function getValue()
	{
		return $this->dateTime ?: NULL;
	}



	/**
	 * @return void
	 */
	public function loadHttpData()
	{
		parent::loadHttpData();

		if (!($this->dateTime = \DateTime::createFromFormat($this->format, $this->value))) {
			$this->valid = FALSE;
		}
	}



	/**
	 * @return Nette\Web\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->value = $this->datetime ? $this->datetime->format($this->format) : $this->value;
		return $control;
	}



	/**
	 * @return boolean
	 */
	public function isValid()
	{
		return $this->valid;
	}



	/****************** Validation Rules ******************/



	/**
	 * @param int|string|\DateTime $time1
	 * @param int|string|\DateTime $time2
	 * @param int|string|\DateInterval $modify
	 * @return string
	 */
	protected static function getDiff($time1, $time2, $modify = NULL)
	{
		$time1 = Nette\DateTime::from($time1);
		$time2 = Nette\DateTime::from($time2);

		if (!$time1 || !$time2) {
			return FALSE;
		}

		if ($modify !== NULL) {
			$time2->modify($modify);
		}

		$time1->setTime(0, 0, 0);
		$time2->setTime(0, 0, 0);

		return $time1->diff($time2)->format('%r%a');
	}



	/**
	 * @param Date $time1
	 * @param int|string|\DateTime $time2
	 * @param int|string|\DateInterval $modify
	 * @return boolean
	 */
	public static function validateMin(DateTime $time1, $time2, $modify = NULL)
	{
		if (is_array($time2)) {
			list($time2, $modify) = $time2;
		}

		$diff = self::getDiff($time1, $time2, $modify);
		return $diff === FALSE ?: $diff < 0;
	}



	/**
	 * @param Date $time1
	 * @param int|string|\DateTime $time2
	 * @param int|string|\DateInterval $modify
	 * @return boolean
	 */
	public static function validateMinOrEqual(DateTime $time1, $time2, $modify = NULL)
	{
		if (is_array($time2)) {
			list($time2, $modify) = $time2;
		}

		$diff = self::getDiff($time1, $time2, $modify);
		return $diff === FALSE ?: $diff <= 0;
	}



	/**
	 * @param Date $time1
	 * @param int|string|\DateTime $time2
	 * @param int|string|\DateInterval $modify
	 * @return boolean
	 */
	public static function validateMaxOrEqual(DateTime $time1, $time2, $modify = NULL)
	{
		if (is_array($time2)) {
			list($time2, $modify) = $time2;
		}

		$diff = self::getDiff($time1, $time2, $modify);
		return $diff === FALSE ?: $diff >= 0;
	}



	/**
	 * @param Date $time1
	 * @param int|string|\DateTime $time2
	 * @param int|string|\DateInterval $modify
	 * @return boolean
	 */
	public static function validateMax(DateTime $time1, $time2, $modify = NULL)
	{
		if (is_array($time2)) {
			list($time2, $modify) = $time2;
		}

		$diff = self::getDiff($time1, $time2, $modify);
		return $diff === FALSE ?: $diff > 0;
	}



	/**
	 * @param Date $control
	 * @return boolean
	 */
	public static function validateValidDate(DateTime $control)
	{
		return $control->isValid();
	}



	/**
	 * @param Nette\Forms\IControl
	 * @return bool
	 */
	public static function validateFilled(Nette\Forms\IControl $control)
	{
		return (string) $control->getValue() !== ''; // NULL, FALSE, '' ==> FALSE
	}

}
