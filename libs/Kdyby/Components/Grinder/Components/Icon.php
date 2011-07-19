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
use Nette\ComponentModel\IComponent;
use Nette\Utils\Html;



/**
 * @author Filip Procházka
 *
 * @property-read Html $prototype
 * @property-read Html $control
 */
class Icon extends Nette\Object implements IImagePlaceholder
{

	/** @var Grinder\Columns\BaseColumn|Grinder\Actions\BaseAction */
	private $parent;

	/** @var Html */
	private $prototype;

	/** @var string|callback */
	private $image;



	public function __construct()
	{
		$this->prototype = Html::el('span', array('class' => 'icon'));
	}



	/**
	 * @param IComponent $parent
	 */
	public function setParent(IComponent $parent)
	{
		$this->parent = $parent;
	}



	/**
	 * @return Html
	 */
	public function getPrototype()
	{
		return $this->prototype;
	}



	/**
	 * @param string|callable|array $image
	 */
	public function setImage($image)
	{
		if (!is_string($image) && !is_callable($image)) {
			throw new Nette\InvalidArgumentException("Given image must be either path, callback or array.");
		}

		$this->image = $image;
	}



	/**
	 * @return Html
	 */
	public function getControl()
	{
		if (!$this->image) {
			return NULL;
		}

		$icon = clone $this->prototype;

		$class = is_callable($this->image)
			? call_user_func($this->image, $this->parent)
			: $this->image;

		return $icon->addClass($class);
	}

}