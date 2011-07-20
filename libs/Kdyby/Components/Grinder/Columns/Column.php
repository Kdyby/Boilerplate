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
use Kdyby\Components\Grinder\Components;
use Nette;
use Nette\Application\UI\Link;
use Nette\Templating\DefaultHelpers;
use Nette\Utils\Html;
use Nette\Utils\Strings;



/**
 * Grid column
 *
 * @author Filip Procházka
 * @license MIT
 *
 * @property bool $sortable
 * @property bool $editable
 */
class Column extends BaseColumn
{

	/** @var string */
	public $dateTimeFormat = "j.n.Y G:i";

	/** @var int */
	public $maxLength = 0;

	/** @var array */
	private $filters = array();

	/** @var bool */
	protected $sortable = TRUE;

	/** @var bool */
	private $editable = FALSE;

	/** @var Components\Image */
	private $image;

	/** @var Components\Link */
	private $link;

	/** @var Nette\Callback */
	private $renderer;



	/**
	 * @param string $caption
	 */
	public function __construct($caption = NULL, Components\IImagePlaceholder $imagePlaceholder = NULL)
	{
		parent::__construct($caption);

		$this->image = $imagePlaceholder ?: new Components\Image;
		$this->image->setParent($this);

		$this->link = new Components\Link($this);
		$this->renderer = callback($this, 'renderValue');
	}



	/**
	 * @param callback $filter
	 * @return Column
	 */
	public function addFilter($filter)
	{
		$this->filters[] = callback($filter);
		return $this;
	}



	/**
	 * @return array
	 */
	public function getFilters()
	{
		return $this->filters;
	}



	/**
	 * @return mixed
	 */
	public function getValue()
	{
		$value = parent::getValue();

		foreach ($this->getFilters() as $filter) {
			$value = $filter($value, $this->getGrid());
		}

		if (is_string($value) && $this->maxLength !== 0) {
			$value = Strings::truncate($value, $this->maxLength);
		}

		return $value;
	}



	/**
	 * @return bool
	 */
	public function isSortable()
	{
		return $this->sortable;
	}



	/**
	 * Set sortable
	 * @param bool $sortable
	 * @return Column
	 */
	public function setSortable($sortable)
	{
		$this->sortable = (bool)$sortable;
		return $this;
	}



	/**
	 * @return bool
	 */
	public function isEditable()
	{
		return $this->editable;
	}



	/**
	 * @param bool $editable
	 * @return Column
	 */
	public function setEditable($editable)
	{
		$this->editable = (bool)$editable;
		return $this;
	}



	/**
	 * @return string|null
	 */
	public function getSorting()
	{
		$grid = $this->getGrid();
		if ($grid->sortColumn === $this->name) {
			return $grid->sortType;
		}

		return NULL;
	}



	/**
	 * @return string|Html
	 */
	public function getHeading()
	{
		if ($this->image->control) {
			return NULL;
		}

		return parent::getHeading();
	}



	/**
	 * @return Html
	 */
	public function getLinkPrototype()
	{
		return $this->link->prototype;
	}



	/**
	 * When given callable, it will receive arguments mapped according to mask and $column object
	 * function (Column $column, array $maskArgs) { ... }
	 *
	 * @param callable|Link $url
	 * @param array $mask
	 * @return Column
	 */
	public function setLink($url, array $mask = array())
	{
		$this->link->setUrl($url);
		$this->link->setMask($mask);
		return $this;
	}



	/**
	 * @return callable|Link
	 */
	public function getUrl()
	{
		return $this->link->url;
	}



	/**
	 * @return array
	 */
	public function getUrlMask()
	{
		return $this->link->urlMask;
	}



	/**
	 * @internal
	 * @return array
	 */
	public function getUrlMaskParams()
	{
		return $this->link->urlMaskParams;
	}



	/**
	 * @return Html
	 */
	public function getLink()
	{
		return $this->link->control;
	}



	/**
	 * @return Html
	 */
	public function getImagePrototype()
	{
		return $this->image->prototype;
	}



	/**
	 * @param string|callable|array $image
	 * @return Column
	 */
	public function setImage($image)
	{
		if (is_array($image)) {
			$image = function (Column $column) use ($image) {
				$value = $column->getValue();
				return isset($image[$value]) ? $image[$value] : reset($image);
			};
		}

		$this->image->setImage($image);
		return $this;
	}



	/**
	 * @return Html
	 */
	public function getImage()
	{
		return $this->image->control;
	}



	/**
	 * @param callable $renderer
	 * @return Column
	 */
	public function setRenderer($renderer)
	{
		$this->renderer = callback($renderer);
		return $this;
	}



	/**
	 * @return Nette\Utils\Html|string
	 */
	public function getControl()
	{
		$control = $this->getLink();
		$image = $this->getImage();

		if (!$control) {
			$control = Html::el();
		}

		if ($image) {
			$control->add($image);
		} else {
			$value = $this->renderer->invoke($this->getValue(), $this);
			$control->{$value instanceof Html ? 'add' : 'setText'}($value);
		}

		return $control;
	}



	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function renderValue($value)
	{
		if (is_bool($value)) {
			return $this->renderBoolean($value);

		} elseif ($value instanceof \DateTime) {
			return $this->renderDateTime($value);
		}

		return $value;
	}



	/**
	 * @param bool $value
	 * @return string
	 */
	protected function renderBoolean($value)
	{
		$value = $value ? 'yes' : 'no';

		if ($this->getGrid()->getContext()->hasService('translator')) {
			return $this->getGrid()->getContext()->translator->translate($value);
		}

		return $value;
	}



	/**
	 * @param string|\Datetime
	 * @return string
	 */
	protected function renderDatetime($date)
	{
		return Nette\DateTime::from($date)
			->format($this->dateTimeFormat);
	}

}