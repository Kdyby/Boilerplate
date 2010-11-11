<?php

namespace Kdyby\Presenter;

use Nette;
use Nette\Environment;
use Nette\Reflection\ClassReflection;
use Nette\String;
use Nette\Web\User;
use Kdyby;



/**
 * Base class for all application presenters.
 */
abstract class Base extends Nette\Application\Presenter
{

	/** @persistent */
	public $language = 'cs';

	/** @persistent */
	public $backlink;

	/** @var \Kdyby\Database\DtM */
	private $DtM;

	/** @var bool (experimental) */
	public $oldLayoutMode = FALSE;

	/** @var bool (experimental) */
	public $oldModuleMode = FALSE;




	/**
	 * @return \Kdyby\Database\DtM
	 */
	public function getDtM()
	{
		if ($this->DtM === NULL) {
			$this->DtM = Environment::getService("Kdyby\\Database\\DtM");
		}

		return $this->DtM;
	}



	/**
	 * @return \Kdyby\Database\DtM
	 */
	public function getDatabaseManager()
	{
		return $this->getDtM();
	}



	/*=========================== Redirecting =============================*/

	

	public function redirectLogin($i)
	{
		if ($this->getUser()->getLogoutReason() === User::INACTIVITY) {
			$this->flashMessage('You have been logged out due to inactivity. Please login again.');
		}

		$backlink = $this->getApplication()->storeRequest();
		$this->redirect('Auth:login', array('backlink' => $backlink));
	}


	public function redirectUnauthorized()
	{

	}


	public function redirectBack($defaultRedirect = Null)
	{
		$backlink = $this->backlink;
		$this->backlink = Null;

		$this->getApplication()->restoreRequest($backlink);
		if ($defaultRedirect !== Null) {
			$this->redirect($defaultRedirect);
		}
	}



	/*=========================== Templates =============================*/

	

	protected function createTemplate()
	{
		$template = parent::createTemplate();
		$template->setTranslator(Environment::getService('Nette\ITranslator'));

		$action = ltrim($this->getAction(TRUE), ':');
		$module = String::lower(substr($action, 0, strpos($action, ':')));
		$theme = Environment::getConfig("theme")->{$module};

		$template->theme = Environment::getVariable('baseUri') . 'theme_' . $theme;
		$template->global = APP_DIR . '/templates';

		$relative = ltrim($this->getAction(TRUE), ':');
		$presenter = substr($relative, 0, strrpos($relative, ':'));
		$modules = explode(':', substr($presenter, 0, strrpos($presenter, ':')));
		array_pop($modules);
		$parentModule = implode(':', $modules);
		$template->parentModule = APP_DIR . '/' .str_replace(':', 'Module/', $parentModule).'Module';

		return $template;
	}


	public function templatePrepareFilters($template)
	{
		parent::templatePrepareFilters($template);

		$template->registerFilter('Nette\Templates\TemplateFilters::netteLinks');
	}



	/*=========================== Standalone Forms initialization =============================*/



	public function createComponent($name)
	{
		$component = parent::createComponent($name);
		if ($component !== Null) {
			return $component;
		}

		if ($m = String::match($name, "~^(?P<form>.+)Form$~")) {
			$ns = $this->reflection->getNamespaceName();
			if (String::match($ns, "~^[^\\\\]+Module$~")) {
				$formClass = $ns . "\\Form\\" . ucfirst($m['form']);
				if (class_exists($formClass)) {
					return $component = new $formClass($this, $name);
				}
			}
	
			$formClass = "\\Kdyby\\Form\\" . ucfirst($m['form']);
			if (class_exists($formClass)) {
				return $component = new $formClass($this, $name);
			}
		}
	}



	/*=========================== Common Components =============================*/


	public function createComponentNavigation($name)
	{
		return $navigation = new Kdyby\Addons\Navigation($this, $name);
	}


	public function createComponentTwitter($name)
	{
		return $twitter = new Kdyby\Addons\Twitter($this, $name);
	}


}