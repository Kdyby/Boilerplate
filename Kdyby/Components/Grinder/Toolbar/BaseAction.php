<?php

namespace Kdyby\Components\Grinder\Toolbar;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
use Kdyby\Components\Grinder\Renderers\IGridRenderer;
use Nette;
use Nette\Forms\ISubmitterControl;



/**
 * @author Filip Procházka
 */
class BaseAction extends Nette\Application\PresenterComponent
{

//	/** @var array */
//	public $reverseToolbarLabel = array(
//		'Nette\Forms\Checkbox',
//		'Nette\Forms\RadioList',
//	);

	/** @var array */
	public $onSubmit = array();

	/** @var IGridRenderer */
	private $renderer = NULL;

	/** @var Nette\Forms\Button */
	private $control;



	/**
	 * @param Nette\Forms\ISubmitterControl $control
	 */
	public function __construct(ISubmitterControl $control)
	{
		$this->control = $control;
		$this->monitor('Kdyby\Components\Grinder\Grid');
	}



	/**
	 * @return Nette\Forms\ISubmitterControl
	 */
	public function getControl()
	{
		return $this->control;
	}



	/**
	 * @param Nette\ComponentContainer $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if ($obj instanceof Grid) {
			$form = $this->getGrid()->getComponent('form');
			$toolbar = $form->getComponent('toolbar');
			$toolbar[$this->name] = $this->control;

			$form->onSubmit[] = array($this, 'fireEvents');
		}
	}



	public function fireEvents()
	{
		$form = $this->getGrid()->getComponent('form');
		if ($form->isSubmittedBy() === $this->control) {
			$this->onSubmit($this->getGrid());
		}
	}



	/**
	 * @return Kdyby\Components\Grinder\Grid
	 */
	public function getGrid()
	{
		return $this->lookup('Kdyby\Components\Grinder\Grid');
	}



	/**
	 * Get cell renderer
	 * @return IGridRenderer
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}



	/**
	 * Set cell renderer
	 * @param IGridRenderer cell renderer
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
	public function render()
	{
		echo call_user_func(array($this->renderer, 'renderToolbarAction'), $this);
	}

}