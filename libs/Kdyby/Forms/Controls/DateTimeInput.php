<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms\Controls;

use DateTime;
use Kdyby;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DateTimeInput extends DateTimeBase
{

	/** @var string */
	public $format = "H:i j.n.Y";



	/**
	 * @param string $caption
	 * @param int $cols
	 */
	public function __construct($caption = NULL, $cols = 10)
	{
		parent::__construct($caption);
		$this->control->type = 'datetime';
		$this->control->size = $cols;
		$this->control->maxlength = 20;
		$this->control->class[] = 'datetime';
	}



	/**
	 * @return string
	 */
	public function getFormat()
	{
		return $this->format;
	}



	/**
	 * @return \DateTime
	 */
	public function getValue()
	{
		return $this->dateTime ?: NULL;
	}



	/**
	 * @param string $name
	 */
	public static function register($name = 'addDatetime')
	{
		Nette\Forms\Container::extensionMethod($name, function (Nette\Forms\Container $container, $name, $label = NULL, $cols = 10, $format = NULL) {
			$control = new Kdyby\Forms\Controls\DateTimeInput($label, $cols);
			if ($format !== NULL) {
				$control->format = $format;
			}
			return $container[$name] = $control;
		});
	}

}
