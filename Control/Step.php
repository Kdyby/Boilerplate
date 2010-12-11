<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Control;

use Kdyby;
use Nette;



/**
 * Description of Step
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Step extends Nette\Application\Control
{

	/** @var string */
	private $title;

	/** @var array */
	public $controlFactory = array();

	/** @var string */
	private $next;

	/** @var array */
	private $onInvalid = array();

	/** @var array */
	private $onValid = array();



	public function __construct($parent, $name, $title = NULL)
	{
		parent::__construct($parent, $name);

		$this->title = $title;

		$this->controlFactory[] = array($parent, 'createStep' . ucfirst($name));
		$this->controlFactory[] = function(AppForm $form) {
			$form->addContainer('actions');
		};
	}



	/**
	 * @param self $control
	 * @throws \InvalidStateException
	 */
	public function attached($control)
	{
		if ($control instanceof self) {
			throw new \InvalidStateException('Nested steps are forbidden.');
		}

		parent::attached($control);
	}



	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}



	/**
	 * @param string $name
	 * @param string $title
	 * @param callback $callback
	 */
	public function addAction($name, $title, $callback = NULL)
	{
		$this->controlFactory[] = function(AppForm $form) use ($name, $title, $callback) {
			$action = $form['actions']->addSubmit($name, $title);
			if ($callback) {
				$action->onClick[] = $callback;
			}
		};
	}



	public function setNext($step, $title)
	{
		if ($class === NULL) {
			$this->addAction('next', $title);
			$this->next = (string)$step;
		}
	}



	/**
	 * @param string $method
	 * @param string|object|NULL $class
	 */
	public function onValid($method, $class = NULL)
	{
		if ($class === NULL) {
			$class = $this->lookup('Kdyby\Control\StepContainer');
		}

		$this->onValid[] = array($class, $method);
	}



	/**
	 * @param string $method
	 * @param string|object|NULL $class
	 */
	public function onInvalid($method, $class = NULL)
	{
		if ($class === NULL) {
			$class = $this->lookup('Kdyby\Control\StepContainer');
		}

		$this->onInvalid[] = array($class, $method);
	}



	public function redirectNext()
	{
		$this->presenter->redirect('this', array('step' => $this->next));
	}



}
