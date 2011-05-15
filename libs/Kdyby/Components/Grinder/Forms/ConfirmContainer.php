<?php

namespace Kdyby\Components\Grinder\Forms;

use Kdyby;
use Kdyby\Components\Grinder\Actions\BaseAction;
use Nette;
use Nette\Forms\Controls\SubmitButton;



/**
 * @author Filip Procházka
 */
class ConfirmContainer extends Nette\Forms\Container
{

	/** @var BaseAction */
	private $action;



	public function __construct(BaseAction $action)
	{
		throw new Nette\NotImplementedException;

		parent::__construct(NULL, NULL);

		$this->action = $action;
		$this->addGroup($action->getConfirmationQuestion($row));

		$this->addSubmit('yes', 'Ano')
			->onClick[] = callback($this, 'YesClicked');

		$this->addSubmit('no', 'Ne')
			->onClick[] = callback($this, 'NoClicked');
	}



	/**
	 * @param SubmitButton $sender
	 */
	public function YesClicked(SubmitButton $sender)
	{
		$this->action->fireEvents();
	}



	/**
	 * @param SubmitButton $sender
	 */
	public function NoClicked(SubmitButton $sender)
	{
		$this->onDeny($this->action);
	}

}