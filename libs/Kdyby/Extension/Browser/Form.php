<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Browser;

use Kdyby;
use Kdyby\Extension\Curl;
use Nette;
use Nette\Http\UrlScript as Url;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Form extends DomElement
{
	/** @var \Kdyby\Extension\Browser\WebPage */
	private $page;

	/** @var string */
	private $action;

	/** @var string */
	private $method;

	/** @var array */
	private $controls;

	/** @var array */
	private $values = array();

	/** @var array */
	private $submits = array();

	/** @var array */
	private $submitNames = array();



	/**
	 * @param \DOMNode $element
	 * @param \Kdyby\Extension\Browser\WebPage $webPage
	 */
	public function __construct(\DOMNode $element, WebPage $webPage)
	{
		parent::__construct($element);
		$this->page = $webPage;
	}



	/**
	 * @return string|\Nette\Http\UrlScript
	 */
	public function getAction()
	{
		if ($this->action === NULL) {
			$this->action = $url = clone $this->page->getAddress();
			if ($action = $this->getElement()->getAttribute('action')) {
				$this->action = $action;
			}
		}

		return $this->action;
	}



	/**
	 * @return string
	 */
	public function getMethod()
	{
		if ($this->method === NULL) {
			$this->method = Kdyby\Extension\Curl\Request::POST;
			if ($method = $this->getElement()->getAttribute('method')) {
				$this->method = strtoupper($method);
			}
		}

		return $this->method;
	}



	/**
	 * @return \DOMElement[]
	 */
	public function getControls()
	{
		if ($this->controls === NULL) {
			$this->loadControls();
		}

		return $this->controls;
	}



	/**
	 * @return \DOMElement[]
	 */
	public function getSubmits()
	{
		if ($this->controls === NULL) {
			$this->loadControls();
		}

		return $this->submits;
	}



	/**
	 * @param string $text
	 * @return \DOMElement|NULL
	 */
	public function findButton($text = NULL)
	{
		if ($text === NULL) {
			if ($names = $this->getSubmitNames()) {
				return $this->submits[reset($names)];
			}
			return NULL;
		}

		foreach ($this->getSubmits() as $submit) {
			if ($this->getSubmitControlValue($submit) === $text) {
				return $submit;

			} elseif ($submit->getAttribute('name') === $text) {
				return $submit;
			}
		}

		return NULL;
	}



	/**
	 * @return string[]
	 */
	public function getSubmitNames()
	{
		if ($this->controls === NULL) {
			$this->loadControls();
		}

		return $this->submitNames;
	}



	/**
	 * TODO: think about appending to lists, not overwriting
	 * @param array $values
	 *
	 * @return \Kdyby\Extension\Browser\Form
	 */
	public function setValues(array $values)
	{
		$this->values = static::mergeTree($values, $this->getValues());
		return $this;
	}



	/**
	 * @return mixed[]
	 */
	public function getValues()
	{
		if ($this->controls === NULL) {
			$this->loadControls();
		}

		return $this->values;
	}



	/**
	 * @param \DOMElement $submitBy
	 * @return array
	 */
	public function getSubmitValues(\DOMElement $submitBy = NULL)
	{
		$submitValue = array();
		if ($submitBy !== NULL) {
			if (!in_array($submitBy, $this->submits, TRUE)) { // todo: maybe ignore?
				throw new Kdyby\InvalidArgumentException("Given button is not inside form.");
			}

			$valueRef =& static::getRef($submitValue, $this->getControlNameKeys($submitBy));
			$valueRef = $this->getSubmitControlValue($submitBy);
		}
		return Arrays::mergeTree($submitValue, $this->getValues());
	}



	/**
	 */
	protected function loadControls()
	{
		$this->submits = $this->values = $this->controls = array();
		foreach ($this->find('input, select, textarea, button') as $control) {
			$keys = $this->getControlNameKeys($control);

			$controlRef =& static::getRef($this->controls, $keys);
			$controlRef = $control;
			unset($controlRef);

			if ($this->isSubmitControl($control)) {
				$this->submitNames[] = $name = $control->getAttribute('name');
				$this->submits[$name] = $control;
				continue;
			}

			$valueRef =& static::getRef($this->values, $keys);
			switch (strtolower($control->nodeName)) {
				case 'input':
					$valueRef = $control->getAttribute('value');
					break;

				case 'select':
					if ($option = $this->findOne('option[selected]', $control)) {
						if ($value = $option->getAttribute('value')) {
							$valueRef = $value;

						} else {
							$valueRef = Strings::normalize(trim($option->textContent));
						}
					}
					break;

				case 'textarea':
					$valueRef = Strings::normalize(trim($control->textContent));
					break;
			}

			unset($valueRef); // fu references
		}
	}



	/**
	 * @param \DOMElement $control
	 * @return array
	 */
	private function getControlNameKeys(\DOMElement $control)
	{
		return explode('[', str_replace(']', '', $control->getAttribute('name')));
	}



	/**
	 * @param \DOMElement $control
	 *
	 * @return boolean
	 */
	private function isSubmitControl(\DOMElement $control)
	{
		$type = strtolower($control->getAttribute('type'));
		return ($node = strtolower($control->nodeName)) === 'button'
			|| ($node === 'input' && in_array($type, array('submit', 'image')));
	}



	/**
	 * @param \DOMElement $control
	 *
	 * @return string
	 */
	private function getSubmitControlValue(\DOMElement $control)
	{
		if ($value = $control->getAttribute('value')) {
			return Strings::normalize($control->getAttribute('value'));
		}

		return Strings::normalize($control->textContent) ?: NULL;
	}



	/**
	 * @param array $arr
	 * @param string|array $key
	 * @return mixed
	 * @throws \Nette\InvalidArgumentException
	 */
	public static function &getRef(&$arr, $key)
	{
		foreach (is_array($key) ? $key : array($key) as $k) {
			if (is_array($arr) || $arr === NULL) {
				if (empty($k)) { // handles "input[]" names
					$arr[] = NULL;
					end($arr);
					$arr = & $arr[key($arr)];
					continue;
				}
				$arr = & $arr[$k];

			} else {
				throw new Nette\InvalidArgumentException('Traversed item is not an array.');
			}
		}
		return $arr;
	}



	/**
	 * @param array $arr1
	 * @param array $arr2
	 *
	 * @return array
	 */
	public static function mergeTree($arr1, $arr2)
	{
		if ($diff = array_diff_key($arr1, $arr2)) {
			$diff = array_map(function ($val) use ($arr1) {
				return array_search($val, $arr1, TRUE);
			}, $diff);
			throw new Kdyby\InvalidArgumentException('Ambiguous key "' . implode('", and "', $diff) . '".');
		}

		$res = $arr1 + $arr2;
		foreach (array_intersect_key($arr1, $arr2) as $k => $v) {
			if (is_array($v) && is_array($arr2[$k])) {
				$res[$k] = static::mergeTree($v, $arr2[$k]);
			}
		}
		return $res;
	}

}
