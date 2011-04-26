<?php

namespace Kdyby\Control;

use Nette;
use Kdyby;



abstract class RepetitiveForm extends LookoutControl
{

	/** @persistent */
	public $fId;



	/**
	 * @param int|string $id
	 * @return string
	 */
	protected function getSignalReceiverName($id)
	{
		return 'form'.$id;
	}



	/**
	 * Pro renderování hromady formulářů pro každý competition zvlášť
	 * @param array|object $defaults
	 */
	public function viewForm($defaults)
	{
		$id = is_array($defaults) ? $defaults['id'] : $defaults->id;
		$form = $this->createAndAttach($id, $defaults);
		$form->render();
	}



	/**
	 * @param Nette\Application\UI\Control $parent
	 */
	protected function attached($parent)
	{
		// vytvoříme formulář, aby mohl příjmout signál
		// z principu stačí vytvořit ve výchozím stavu pouze jeden formulář,
		// protože nette dovoluje zpracovat pouze jeden signál v jednom requestu
		$this->createAndAttach($this->fId);
		// o zbytek se postará životní cyklus

		parent::attached($parent);
	}



	/**
	 * @param int|string $id
	 * @return Nette\Application\UI\Form
	 */
	protected function createAndAttach($id, $defaults = NULL)
	{
		$name = $this->getSignalReceiverName($id);
		$this[$name] = $this->createForm($id, $defaults);

		// tady je trošku magie, možná to bude chtít poladit, aby to generovalo správný tvar
		$fullname = $this->getUniqueId();
		$this[$name]->action->setParam($fullname . self::NAME_SEPARATOR . 'fId', $id);

		return $this[$name];
	}



	abstract protected function createForm($id, $defaults = NULL);

}