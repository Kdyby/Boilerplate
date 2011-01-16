<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Form;

use Nette;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class StateForm extends BaseForm
{


	protected function attached($presenter)
	{
		parent::attached($presenter);

		$state = $this->getState();

		if ($state->errors) {
			$this->setValues($state->values);
			foreach($state->errors as $error) {
				$this->addError($error);
			}
		}

		$state->values = $state->errors = array();
		$this->saveState($state);
	}



	/**
	 * @param array $post
	 * @return Kdyby\Form\StateForm
	 */
	public function recieve(array $post)
	{
		$this->setValues($post);
		$this->validate();

		$state = $this->getState();
		$state->errors = $this->getErrors();
		$state->values = $post;
		$this->saveState($state);
		
		return $this;
	}



	/**
	 * @param string $error
	 */
	public function saveError($error)
	{
		$this->addError($error);

		$state = $this->getState();
		$state->errors = $this->getErrors();
		$this->saveState($state);
	}



	/**
	 * @return array
	 */
	private function getState()
	{
		$session = Nette\Environment::getSession("Kdyby.Form.State");
		return (object)(isset($session[$this->name]) ? $session[$this->name] : array('errors'=>array(),'values'=>array()));
	}



	/**
	 * @return array
	 */
	private function saveState($state)
	{
		return Nette\Environment::getSession("Kdyby.Form.State")->offsetSet($this->name, $state);
	}

}
