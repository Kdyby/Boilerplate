<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Columns;

use Kdyby;
use Nette;
use Nette\Utils\Html;



/**
 * @author Filip Procházka
 */
class ImageColumn extends Column
{

	/** @var callback|string */
	private $image;



	/**
	 * @param string|callable|Html $image
	 */
	public function __construct($image)
	{
		parent::__construct();

		if (!is_string($image) && !is_callable($image) && !$image instanceof Html) {
			throw new Nette\InvalidArgumentException("Given image must be either path, callback or Nette\\Utils\\Html.");
		}

		$this->image = $image;
	}



	/**
	 * @param callback $filter
	 * @throws Nette\NotSupportedException
	 */
	public function addFilter($filter)
	{
		throw new Nette\NotSupportedException;
	}



	/**
	 * @return string|Html
	 */
	public function getImage()
	{
		if (!$this->image) {
			throw new Nette\InvalidStateException("The image is missing");
		}

		$expand = callback($this->getGrid()->getContext(), 'expand');
		$expand = function ($value) use ($expand) {
			return $expand(substr_count($value, '%') ? $value : '%basePath%/' . $value);
		};

		$image = is_callable($this->image) ? call_user_func($this->image, $this) : $this->image;

		if ($image instanceof Html) {
			return clone $image;
		}

		return Html::el('img', array(
				'src' => $expand($image),
				'alt' => $this->getCaption()
			));
	}



	/**
	 * @return Nette\Utils\Html|string
	 */
	public function getControl()
	{
		$image = $this->getImage();
		$image = $image instanceof Html ? $image : Html::el()->setHtml($image);

		$link = $this->getLink() ?: NULL;
		if ($link) {
			$image = Html::el('a', array(
				'href' => $link
			))->add($image);
		}

		return $image;
	}

}