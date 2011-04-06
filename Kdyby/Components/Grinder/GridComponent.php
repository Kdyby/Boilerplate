<?php

namespace Kdyby\Components\Grinder;

use Kdyby;
use Nette;
use Kdyby\Components\Grinder\Grid;
use Kdyby\Components\Grinder\Renderers\IGridRenderer;



/**
 * @author Filip Procházka
 *
 * @property string $caption
 * @property Kdyby\Components\Grinder\Renderers\IGridRenderer $renderer
 */
class GridComponent extends Nette\Application\PresenterComponent
{

	/** @var string */
	private $caption;

	/** @var Kdyby\Components\Grinder\Renderers\IGridRenderer */
	private $renderer;



	public function __construct()
	{
		parent::__construct(NULL, NULL);

		$this->monitor('Kdyby\Components\Grinder\Grid');
		$this->monitor('Nette\Application\IPresenter');
	}



	/**
	 * Set caption
	 * @param string caption
	 * @return BaseButton
	 */
	public function setCaption($caption)
	{
		$this->caption = $caption;
		return $this;
	}



	/**
	 * Get caption
	 * @return string
	 */
	public function getCaption()
	{
		return $this->caption;
	}



	/**
	 * Get cell renderer
	 * @return Kdyby\Components\Grinder\Renderers\IGridRenderer
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}



	/**
	 * Set cell renderer
	 * @param Kdyby\Components\Grinder\Renderers\IGridRenderer cell renderer
	 * @return Column
	 */
	public function setRenderer(IGridRenderer $cellRenderer)
	{
		$this->renderer = $cellRenderer;
		return $this;
	}



	/**
	 * @return void
	 */
	abstract public function render();



	/**
	 * @return Kdyby\Components\Grinder\Grid
	 */
	public function getGrid()
	{
		return $this->lookup('Kdyby\Components\Grinder\Grid');
	}

}