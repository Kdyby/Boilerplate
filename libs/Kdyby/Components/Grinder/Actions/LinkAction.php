<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Actions;

use Kdyby;
use Kdyby\Components\Grinder\Components;
use Nette;
use Nette\Application\UI\Link;
use Nette\Utils\Html;



/**
 * @author Filip Procházka
 */
class LinkAction extends BaseAction
{

	/** @var Html */
	private $linkPrototype;

	/** @var Components\Image */
	private $image;



	public function __construct()
	{
		parent::__construct();

		$this->image = new Components\Image($this);
		$this->linkPrototype = Html::el('a');
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
	 * @return LinkAction
	 */
	public function setImage($image)
	{
		if (is_array($image)) {
			$image = function (LinkAction $action) use ($image) {
				return $image[$action->grid->getRecordProperty($action->realName)];
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
	 * @return Html
	 */
	public function getLinkPrototype()
	{
		return $this->linkPrototype;
	}



	/**
	 * @return string
	 */
	public function getLink()
	{
		$grid = $this->getGrid();
		return $grid->lazyLink('action!', array(
			'action' => $this->name,
			'token' => $grid->getSecurityToken(),
			'id' => $grid->getModel()->getUniqueId($grid->getCurrentRecord()) ?: NULL,
		));
	}



	/**
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$control = clone $this->linkPrototype;
		$control->href = $this->getLink();

		if ($image = $this->getImage()) {
			$control->add($image);

		} else {
			$caption = $this->getCaption();
			$control->{$caption instanceof Html ? 'add' : 'setText'}($caption);
		}

		return $control;
	}

}