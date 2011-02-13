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


namespace Kdyby\Forms;

use Nette;
use Nette\Application\AppForm;
use Nette\Environment;



/**
 * Description of Login
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class BaseForm extends AppForm
{

	public $onSuccess = array();
	public $onError = array();

	private $template;



	public function __construct(Nette\IComponentContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		// translator
		$this->setTranslator(Environment::getService('Nette\ITranslator'));
		$this->addProtection("Ouchie! Please try to submit the form again, the delivery boy forgot something!");

//		$this->addGroup();
//		$persistents = $this->addContainer("persistents");
	}


	public function getTemplate()
	{
		if ($this->template === NULL) {
			$this->template = clone $this->presenter->template;
			$this->template->form = $this;
		}

		return $this->template;
	}


	/**
	 * @return Nette\Web\User
	 */
	protected function getUser()
	{
		return $this->presenter->user;
	}



	/**
	 * Returns a fully-qualified name that uniquely identifies the component
	 * within the presenter hierarchy.
	 * @return string
	 */
	public function getUniqueId()
	{
		return $this->lookupPath('Nette\Application\Presenter', TRUE);
	}



//	public function render() // wtf?
//	{
//		$args = func_get_args();
//
//		if( !empty($this->templateFile) AND empty($args) ){
//			$template = $this->getTemplate();
//			$template->setFile($this->templateFile);
//
//			$template->form = $this;
//			$template->render();
//
//		} elseif( PHP_VERSION_ID >= 50300 ){
//			return call_user_func_array(array('parent', 'render'), $args);
//
//		} else {
//			return call_user_func_array(array($this, 'parent::render'), $args);
//		}
//	}

}

Nette\Forms\FormContainer::extensionMethod('addCheckboxList', array('Kdyby\Forms\Controls\CheckboxList', 'addCheckboxList'));
