<?php

namespace Kdyby\Components\Grinder\Actions;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
use Kdyby\Components\Grinder\Renderers\IGridRenderer;
use Nette;
use Nette\Forms\Form;
use Nette\Forms\ISubmitterControl;



/**
 * @author Filip Procházka
 */
class FormAction extends BaseAction
{

	/** @var array */
	public $onSubmit = array();

	/** @var Nette\Forms\Button */
	private $control;



	/**
	 * @param Nette\Forms\ISubmitterControl $control
	 */
	public function __construct(ISubmitterControl $control)
	{
		parent::__construct();

		$this->control = $control;
	}



	/**
	 * @param Nette\ComponentContainer $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Grid) {
			return;
		}

		$form = $this->getGrid()->getForm();
		$toolbar = $form->getComponent('toolbar');
		$toolbar[$this->name] = $this->control;

		$form->onSubmit[] = array($this, 'fireEvents');
	}



	public function fireEvents()
	{
		$form = $this->getGrid()->getForm();
		if ($form->isSubmitted() === $this->control) {
			$this->onSubmit($this);
		}
	}



	/**
	 * @return Nette\Forms\ISubmitterControl
	 */
	public function getControl()
	{
		return $this->control;
	}

}