<?php

namespace Kdyby\Type;

use Nette;
use Kdyby;



/**
 * Description of Enum
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Enum extends Nette\Object
{

	/** @var array */
	private $values;

	/** @var scalar */
	private $value;



	/**
	 * @param array $values
	 */
	public function __construct(array $values)
	{
		$this->values = $values;
	}



	/**
	 * @return array
	 */
	public function getValues()
	{
		return $this->values;
	}
	


	/**
	 * @return scalar
	 */
	public function getValue()
	{
		return $this->value;
	}



	/**
	 * @param scalar $value
	 * @return Enum
	 */
	public function setValue($value)
	{
		if (in_array($value, $this->values)) {
			$this->value = $value;
		}

		return $this;
	}

}
