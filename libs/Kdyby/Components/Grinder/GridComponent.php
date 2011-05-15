<?php

namespace Kdyby\Components\Grinder;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
use Kdyby\Components\Grinder\Renderers\IGridRenderer;
use Nette;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Html;



/**
 * @author Filip Procházka
 *
 * @property string|Html $caption
 * @property IGridRenderer $renderer
 */
abstract class GridComponent extends Nette\Application\UI\PresenterComponent
{

	/** @var string|Html */
	private $caption;

	/** @var IGridRenderer */
	private $renderer;



	public function __construct()
	{
		parent::__construct(NULL, NULL);

		$this->monitor('Kdyby\Components\Grinder\Grid');
		$this->monitor('Nette\Application\UI\Presenter');
	}



	/**
	 * @param IContainer $parent
	 * @throws Nette\InvalidStateException
	 */
	protected function validateParent(IContainer $parent)
	{
		parent::validateParent($parent);

		if (!$parent instanceof Grid) {
			$grid = $parent->lookup('Kdyby\\Components\\Grinder\\Grid', FALSE);

			if (!$grid instanceof Grid) {
				throw new Nette\InvalidStateException("Parent or one of ancesors must be instance of Kdyby\\Components\\Grinder\\Grid.");
			}
		}
	}



	/**
	 * @param string|Html caption
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
	 * @return string|Html
	 */
	public function getCaption()
	{
		return $this->caption;
	}



	/**
	 * @return IGridRenderer
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}



	/**
	 * @param IGridRenderer $cellRenderer
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
	 * @return Grid
	 */
	public function getGrid()
	{
		return $this->lookup('Kdyby\Components\Grinder\Grid');
	}

}