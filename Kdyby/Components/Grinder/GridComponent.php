<?php

namespace Kdyby\Components\Grinder;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
use Kdyby\Components\Grinder\Renderers\IGridRenderer;
use Nette;
use Nette\Web\Html;



/**
 * @author Filip Procházka
 *
 * @property string|Nette\Web\Html $caption
 * @property Kdyby\Components\Grinder\Renderers\IGridRenderer $renderer
 */
abstract class GridComponent extends Nette\Application\PresenterComponent
{

	/** @var string|Nette\Web\Html */
	private $caption;

	/** @var Kdyby\Components\Grinder\Renderers\IGridRenderer */
	private $renderer;



	public function __construct()
	{
		parent::__construct(NULL, NULL);

		$this->monitor('Kdyby\Components\Grinder\Grid');
		$this->monitor('Nette\Application\Presenter');
	}



	/**
	 * @param string|Nette\Web\Html caption
	 * @return GridComponent
	 */
	public function setCaption($caption)
	{
		if ($caption && !is_string($caption) && !$caption instanceof Html) {
			throw new \InvalidArgumentException("Given caption must be either string or instance of Nette\\Web\\Html, " . gettype($caption) . " given.");
		}

		$this->caption = $caption;
		return $this;
	}



	/**
	 * @return string|Nette\Web\Html
	 */
	public function getCaption()
	{
		return $this->caption;
	}



	/**
	 * @return Kdyby\Components\Grinder\Renderers\IGridRenderer
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}



	/**
	 * @param Kdyby\Components\Grinder\Renderers\IGridRenderer $cellRenderer
	 * @return GridComponent
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