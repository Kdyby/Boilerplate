<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Components;

use Kdyby;
use Kdyby\Components\Grinder;
use Nette;
use Nette\Application\UI\Link as NLink;
use Nette\Utils\Html;



/**
 * @author Filip Procházka
 *
 * @property-read Html $prototype
 * @property-read callable|Link $url
 * @property-read array $urlMask
 * @property-read array $urlMaskParams
 * @property-read Html $control
 */
class Link extends Nette\Object
{

	/** @var Grinder\Columns\BaseColumn|Grinder\Actions\BaseAction */
	private $parent;

	/** @var Html */
	private $prototype;

	/** @var string|callback */
	private $url;

	/** @var array */
	private $urlMask = array();



	/**
	 * @param Grinder\Columns\BaseColumn|Grinder\Actions\BaseAction $parent
	 */
	public function __construct($parent)
	{
		$this->parent = $parent;
		$this->prototype = Html::el('a');
	}



	/**
	 * @return Html
	 */
	public function getPrototype()
	{
		return $this->prototype;
	}



	/**
	 * When given callable, it will receive arguments mapped according to mask and $column object
	 * function (Column $column, array $maskArgs) { ... }
	 *
	 * @param callable|NLink $url
	 * @param array $mask
	 */
	public function setUrl($url)
	{
		if (!is_callable($url) && !$url instanceof NLink) {
			throw new Nette\InvalidArgumentException("URL must be callable or instance of Nette\\Application\\UI\\Link");
		}

		$this->url = $url;
	}



	/**
	 * @param array $mask
	 */
	public function setMask(array $mask)
	{
		$this->urlMask = $mask;
	}



	/**
	 * @internal
	 * @return array
	 */
	public function getUrlMaskParams()
	{
		$args = array();
		foreach ((array)$this->urlMask as $argName => $paramName) {
			$args[$argName] = $this->parent->grid->getRecordProperty($paramName);
		}
		return $args;
	}



	/**
	 * @return Nette\Application\UI\Link|NULL
	 */
	public function getUrl()
	{
		if (!$this->url) {
			return NULL;
		}

		$maskArgs = $this->getUrlMaskParams();
		$url = is_callable($this->url)
			? call_user_func($this->url, $this->parent, $maskArgs)
			: $this->url;

		if (!$url instanceof NLink) {
			throw new Nette\InvalidStateException("URL is not instanceof Nette\\Application\\UI\\Link, " . gettype($url) . " given.");
		}

		foreach ($maskArgs as $argName => $value) {
			$url->setParam($argName, $value);
		}

		return $url;
	}



	/**
	 * @return Html|NULL
	 */
	public function getControl()
	{
		$url = $this->getUrl();

		if (!$url) {
			return NULL;
		}

		$link = clone $this->prototype;
		$link->href($url);

		return $link;
	}

}