<?php

namespace Kdyby\Tools;

use Kdyby;
use Nette;
use Nette\Reflection\ClassType;



/**
 * @author Filip Procházka
 */
class JsonSerializer extends Nette\Object
{

	/** @var Nette\Callback */
	private $encoder;

	/** @var Nette\Callback */
	private $decoder;



	/**
	 * @param callable $encoder
	 * @param callable $decoder
	 */
	public function __construct($encoder = NULL, $decoder = NULL)
	{
		$encode = $encode ?: 'Nette\Utils\Json::encode';
		$decoder = $decoder ?: 'Nette\Utils\Json::decode';

		if (!is_callable($encoder)) {
			throw new Nette\InvalidArgumentException("Given encoder is not callable");
		}

		if (!is_callable($decoder)) {
			throw new Nette\InvalidArgumentException("Given decoder is not callable");
		}

		$this->encoder = callback($encoder);
		$this->decoder = callback($decoder);
	}



	/**
	 * @param object|array $data
	 */
	public function encode($data)
	{
		return $this->encoder->invoke($this->doPrepareToEncode($data));
	}



	/**
	 * @param mixed $data
	 * @return mixed
	 */
	private function doPrepareToEncode($data)
	{
		if (is_scalar($data)) {
			return $data;

		} else {
			if (is_object($data)) {
				if ($data instanceof IJsonSerializable) {
					$object = (object)$data->toJson();

				} else {
					$object = (object)array();

					$classRef = new ClassType($data);
					foreach ($classRef->getProperties() as $property) {
						$property->setAccessible(TRUE);
						$object->{$property->getName()} = $property->getValue($data);
					}
				}

				foreach ($object as &$value) {
					$value = $this->doPrepareToEncode($value);
				}

				$object->___type = get_class($data);
				return $object;

			} elseif (is_array($data)) {
				foreach ($data as &$value) {
					$value = $this->doPrepareToEncode($value);
				}

				return $data;
			}
		}

		throw new Nette\NotImplementedException();
	}



	/**
	 * @param string $data
	 */
	public function decode($data)
	{
		return $this->doDecode($this->decoder->invoke($data));
	}



	/**
	 * @param array|object $data
	 */
	private function doDecode($data)
	{
		if (is_scalar($data)) {
			return $data;

		} else {
			if (is_object($data)) {
				if (!class_exists($classType = $data->___type)) {
					throw new Nette\InvalidStateException("Class '" . $classType . "' not found");
				}

				unset($data->___type);
				foreach ($data as &$value) {
					$value = $this->doDecode($value);
				}

				$prototype = unserialize(sprintf('O:%d:"%s":0:{}', strlen($classType), $classType));

				if ($prototype instanceof IJsonSerializable) {
					$prototype->fromJson((array)$data);

				} else {
					$classRef = new ClassType($classType);
					foreach ($data as $property => $value) {
						$propRef = $classRef->getProperty($property);
						$propRef->setAccessible(TRUE);
						$propRef->setValue($prototype, $value);
					}
				}

				return $prototype;

			} elseif (is_array($data)) {
				foreach ($data as &$value) {
					$value = $this->doDecode($value);
				}

				return $data;
			}
		}

		throw new Nette\NotImplementedException();
	}

}



/**
 * @author Filip Procházka
 */
interface IJsonSerializable
{

	/**
	 * pairs of property => value
	 *
	 * @return array to serialize from class
	 */
	function toJson();

	/**
	 * values for the class to handle
	 *
	 * @param array $json
	 */
	function fromJson($json);

}