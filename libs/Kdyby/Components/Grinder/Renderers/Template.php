<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Renderers;

use Kdyby;
use Kdyby\Components\Grinder;
use Nette;
use Nette\Templating\Template as NTemplate;



/**
 * @author Filip Procházka
 */
class Template extends Nette\Object
{

	/** @var NTemplate */
	private $template;

	/** @var Grinder\Grid */
	private $grid;



	/**
	 * @param Grinder\Grid $grid
	 * @param NTemplate $template
	 */
	public function __construct(Grinder\Grid $grid, NTemplate $template)
	{
		$this->grid = $grid;
		$this->template = $template;
	}



	/**
	 * @return NTemplate
	 */
	public function getTemplate()
	{
		return $this->template;
	}



	/**
	 * @return void
	 */
	public function render()
	{
		$this->template->renderer = $this;
		$this->template->grid = $this->grid;
		$this->template->toolbar = $this->grid->getToolbar();
		$this->template->form = $this->grid->getForm();
		$this->template->paginator = $this->grid->getVisualPaginator();
		$this->template->render();
	}

}