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
use Nette\Forms\Container;
use Nette\Utils\Html;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property \Nette\Utils\Html|\stdClass $control
 */
class DateTimeInput extends Nette\Forms\Controls\BaseControl
{
	const TYPE_DATE = 'date';
	const TYPE_TIME = 'time';
	const TYPE_DATETIME = 'date time';

	/** @var array */
	public static $jsFormat = array(
		'd' => "dd",
		'j' => "d",
		'm' => "mm",
		'n' => "m",
		'z' => "o",
		'Y' => "yy",
		'y' => "y",
		'U' => "@",
		'h' => "h",
		'H' => "hh",
		'g' => "g",
		'A' => "TT",
		'i' => "mm",
		's' => "ss",
		'G' => "h",
	);

	/** @var string */
	public $type = self::TYPE_DATETIME;

	/** @var array */
	public $format = array(
		'time' => 'G:i',
		'date' => 'j.n.Y'
	);

	/** @var array */
	public $size = array(
		'time' => 4,
		'date' => 10,
	);

	/** @var array */
	public $class = array(
		'time' => 'input-mini',
		'date' => 'input-small',
	);

	/** @var \Nette\Utils\Html */
	private $container;

	/** @var \Datetime */
	private $datetime;



	/**
	 * @param string $caption
	 */
	public function __construct($caption = NULL)
	{
		parent::__construct($caption);
		$this->control->type = 'text';
		$this->container = Html::el();
	}



	/**
	 * @param array|\Datetime $value
	 * @return \Nette\Forms\Controls\BaseControl
	 */
	public function setValue($value)
	{
		$this->datetime = NULL;
		$this->value = $value;
		if (!$value) {
			return $this;
		}

		try {
			if (is_array($value)) {
				$value = $this->combineValue($value);
				if (!$this->datetime = (\Datetime::createFromFormat('!' . $this->getFormat(), $value) ?: NULL)) {
					$this->datetime = \Datetime::createFromFormat('!Y-m-d H:i:s', $value) ?: NULL;
				}
			}

			if (!$this->datetime) {
				$this->datetime = Nette\DateTime::from($value) ? : NULL;
			}

		} catch (\Exception $e) { }

		if (!$this->datetime || isset($e)) {
			// here should never be object in $value
			$given = implode(' ', (array)$value);
			$this->addError("Given date or time '$given' is not in required format '{$this->getFormat()}'.");
		}

		return $this;
	}



	/**
	 * @return array
	 */
	protected function getParts()
	{
		return explode(' ', $this->type);
	}



	/**
	 * @throws \Kdyby\InvalidStateException
	 * @return string
	 */
	public function getFormat()
	{
		$formats = $this->format;
		return implode('', array_map(function ($part) use ($formats) {
			if (!isset($formats[$part])) {
				throw new \Kdyby\InvalidStateException("Format for $part is missing, please provide it writing in \$control->format['$part'].");
			}
			return $formats[$part];
		}, $this->getParts()));
	}



	/**
	 * @param array $value
	 *
	 * @throws \Kdyby\InvalidStateException
	 * @return string
	 */
	protected function combineValue(array $value)
	{
		return implode('', array_map(function ($part) use ($value) {
			if (!isset($value[$part])) {
				throw new \Kdyby\InvalidStateException("Value of $part is missing.");
			}
			return $value[$part];
		}, $this->getParts()));
	}



	/**
	 * @return \Datetime|NULL
	 */
	public function getValue()
	{
		return ($this->value && $this->datetime) ? $this->datetime : NULL;
	}



	/**
	 * Generates label's HTML element.
	 * @param string $caption
	 *
	 * @return \Nette\Utils\Html
	 */
	public function getLabel($caption = NULL)
	{
		/** @var \Nette\Utils\Html|\stdClass $label */
		$label = parent::getLabel();
		$parts = $this->getParts();
		$label->for .= '-' . reset($parts);
		return $label;
	}



	/**
	 * Generates controls HTML element.
	 * @return \Nette\Utils\Html
	 */
	public function getControl()
	{
		/** @var \Nette\Utils\Html|\stdClass $control */
		$control = parent::getControl();
		$container = clone $this->container;
		$id = $control->id;
		$name = $control->name;

		foreach ($this->getParts() as $part) {
			$control->id = "$id-$part";
			$control->name = $name . "[$part]";
			$control->type = $part;
			$control->size = $this->size[$part];
			$control->class[] = $part;
			$control->class[] = $this->class[$part];
			$control->value = $this->getValue() ? $this->getValue()->format($this->format[$part]) : NULL;
			$control->data('kdyby-format', strtr($this->format[$part], static::$jsFormat));

			$container->add((string)$control);
		}

		return $container;
	}


	/********************* Validation rules ************************/


	/**
	 * @param \Kdyby\Forms\Controls\DateTimeInput|int|string $time1
	 * @param \Kdyby\Forms\Controls\DateTimeInput|int|string $time2
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
	 * @param \Kdyby\Forms\Controls\DateTimeInput|int|string $time
	 *
	 * @return \DateTime|NULL
	 */
	private static function datetimeFrom($time)
	{
		if ($time instanceof self) {
			$time = $time->datetime;
			$time = $time ? clone $time : FALSE;

		} else {
			$time = Nette\DateTime::from($time);
		}

		return $time ? : NULL;
	}



	/**
	 * @param \Kdyby\Forms\Controls\DateTimeInput $time1
	 * @param \DateTime|int|string $time2
	 * @param string|\DateInterval $modify
	 *
	 * @return bool
	 */
	public static function validateMin(DateTimeInput $time1, $time2, $modify = NULL)
	{
		if (is_array($time2)) {
			list($time2, $modify) = $time2;
		}

		return ($diff = self::getDiff($time1, $time2, $modify)) === FALSE ? : $diff < 0;
	}



	/**
	 * @param \Kdyby\Forms\Controls\DateTimeInput $time1
	 * @param \DateTime|int|string $time2
	 * @param string|\DateInterval $modify
	 *
	 * @return bool
	 */
	public static function validateMinOrEqual(DateTimeInput $time1, $time2, $modify = NULL)
	{
		if (is_array($time2)) {
			list($time2, $modify) = $time2;
		}

		return ($diff = self::getDiff($time1, $time2, $modify)) === FALSE ? : $diff <= 0;
	}



	/**
	 * @param \Kdyby\Forms\Controls\DateTimeInput $time1
	 * @param \DateTime|int|string $time2
	 * @param string|\DateInterval $modify
	 *
	 * @return bool
	 */
	public static function validateMaxOrEqual(DateTimeInput $time1, $time2, $modify = NULL)
	{
		if (is_array($time2)) {
			list($time2, $modify) = $time2;
		}

		return ($diff = self::getDiff($time1, $time2, $modify)) === FALSE ? : $diff >= 0;
	}



	/**
	 * @param \Kdyby\Forms\Controls\DateTimeInput $time1
	 * @param \DateTime|int|string $time2
	 * @param string|\DateInterval $modify
	 *
	 * @return bool
	 */
	public static function validateMax(DateTimeInput $time1, $time2, $modify = NULL)
	{
		if (is_array($time2)) {
			list($time2, $modify) = $time2;
		}

		return ($diff = self::getDiff($time1, $time2, $modify)) === FALSE ? : $diff > 0;
	}



	/**
	 * @param \Kdyby\Forms\Controls\DateTimeInput $control
	 *
	 * @return bool
	 */
	public static function validateValidDate(DateTimeInput $control)
	{
		return !$control->hasErrors();
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



	/**
	 * Registers methods addDate, addTime & addDatetime to form Container class.
	 */
	public static function register()
	{
		Container::extensionMethod('addDate', function (Container $container, $name, $label = NULL) {
			$control = new DateTimeInput($label);
			$control->type = DateTimeInput::TYPE_DATE;
			return $container[$name] = $control;
		});

		Container::extensionMethod('addTime', function (Container $container, $name, $label = NULL) {
			$control = new DateTimeInput($label);
			$control->type = DateTimeInput::TYPE_TIME;
			return $container[$name] = $control;
		});

		Container::extensionMethod('addDatetime', function (Container $container, $name, $label = NULL) {
			$control = new DateTimeInput($label);
			$control->type = DateTimeInput::TYPE_DATETIME;
			return $container[$name] = $control;
		});
	}

}
