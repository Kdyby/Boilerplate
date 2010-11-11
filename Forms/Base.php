<?php

namespace Kdyby\Form;

use Nette;
use Nette\Application\AppForm;
use Nette\Environment;



/**
 * Description of Login
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Base extends AppForm
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

}

Nette\Forms\FormContainer::extensionMethod('addCheckboxList', array('Kdyby\Form\Control\CheckboxList', 'addCheckboxList'));
