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
use Nette\Utils\Html;



/**
 * @author Filip Procházka
 *
 * @property-read Html $prototype
 * @property-read Html $control
 */
class Image extends Nette\Object
{

	/** @var Grinder\Columns\BaseColumn|Grinder\Actions\BaseAction */
	private $parent;

	/** @var Html */
	private $prototype;

	/** @var string|callback */
	private $image;



	/**
	 * @param Grinder\Columns\BaseColumn|Grinder\Actions\BaseAction $parent
	 */
	public function __construct($parent)
	{
		$this->parent = $parent;
		$this->prototype = Html::el('img', array('alt' => ''));
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

		$image = clone $this->prototype;
		$image->alt = $this->parent->caption;

		$src = is_callable($this->image)
			? call_user_func($this->image, $this->parent)
			: $this->image;

		$image->src = $this->parent->grid->getContext()->expand(
				substr_count($src, '%') ? $src : '%basePath%/' . $src
			);

		return $image;
	}

}