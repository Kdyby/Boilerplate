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
 * @property-read Kdyby\Application\DatabaseManager $dtm
 */
abstract class BasePresenter extends Nette\Application\Presenter
{

	/** @persistent */
	public $language = 'cs';

	/** @persistent */
	public $backlink;

	/** @var Kdyby\Application\DatabaseManager */
	private $databaseManager;

	/** @var bool (experimental) */
	public $oldLayoutMode = FALSE;

	/** @var bool (experimental) */
	public $oldModuleMode = FALSE;

	/** @var Nette\ITranslator */
	private $translator;



	/**
	 * @return Kdyby\Application\DatabaseManager
	 */
	public function getDtm()
	{
		return $this->getDatabaseManager();
	}



	/**
	 * @return Kdyby\Application\DatabaseManager
	 */
	public function getDatabaseManager()
	{
		if ($this->databaseManager === NULL) {
			$this->databaseManager = Environment::getService('Kdyby\Application\DatabaseManager');
		}

		return $this->databaseManager;
	}



	/**
	 * @return Nette\Web\IUser
	 */
	public function getUser()
	{
		return Nette\Environment::getUser();
	}



	/**
	 * @return Nette\ITranslator
	 */
	public function getTranslator()
	{
		if ($this->translator === NULL) {
			$this->translator = Environment::getService("Nette\\ITranslator");
		}

		return $this->translator;
	}



	/**
	 * @param Nette\ITranslator $translator
	 */
	public function setTranslator(Nette\ITranslator $translator)
	{
		$this->translator = $translator;
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



	protected function setLayoutFile($file)
	{
		if (!file_exists($file)) {
			throw new \FileNotFoundException("Requested template '".$file."' is missing.");
		}

		$this->layout = FALSE;
		$this->template->layout = $file;
		$this->template->_extends = $file;
	}



	/**
	 * <code>
	 * Examples for common "app/FrontModule/presenters/HomepagePresenter.php"
	 * ":Front/something" -> "app/FrontModule/templates/something.latte"
	 * ":/something" -> "app/templates/something.latte"
	 * "../something" -> "app/templates/something.latte"
	 * "../../something" -> Exception
	 * "../Client:Setup/something" -> "app/ClientModule/SetupModule/templates/something.latte"
	 * </code>
	 *
	 * @param string $search
	 * @return string
	 */
	public function searchTemplate($search)
	{
		$action = $this->getAction(TRUE);
		$ns = explode(':', trim(substr($action, 0, strrpos($action, ':')), ':'));

		if (substr_count($search, '/') > 0) {
			$ex = (int)strrpos($search, '/');
			$nettePath = substr($search, 0, $ex);
			$path = substr($search, ($ex>0 ? $ex+1 : 0));

			if (substr($nettePath, 0, 1) === ':') {
				// absolute ":Front/something.latte"
				// absolute ":/something.latte"

				$ns = array_filter(
						String::split(trim($nettePath, ':/'), '~:~'),
						function($v){ return (bool)$v; }
					);
				$file = $path;

			} elseif (substr($nettePath, 0, 3) === '../') {
				// relative "../@layout.latte"
				// relative "../../something.latte"
				// relative "../Client:Setup/something.latte"

				while (substr($nettePath, 0, 3) == '../') {
					if (count($ns) === 0) {
						throw new \InvalidArgumentException("Error in search query '".$search."', are you trying to jump out of app dir? Sorry, can't do that.");
					}

					array_pop($ns);
					$nettePath = substr($nettePath, 3);
				}

				$relativePath = array_filter(
						String::split(trim($nettePath, ':/'), '~:~'),
						function($v){ return (bool)$v; }
					);
				//dump($ns, $relativePath);die();
				$ns = array_merge((array)$ns, $relativePath);
				$module = ($ns ? "\\". implode("Module\\", $ns).'Module' : NULL);
				$file = $path;
			}

		} else {
			$file = $search;
		}

		$file = APP_DIR . '/' . // app dir
			($ns ? implode('Module/', $ns) . 'Module/' : NULL) . 'templates/' . // path to templates dir
			$file . '.latte'; // filename

		if (!file_exists($file)) {
			if (file_exists(substr($file, 0, -5).'phtml')) { // depracated
				throw new \FileNotFoundException("Requested template '".substr($file, 0, -5)."phtml' should be using '.latte' extension.");
			}

			throw new \FileNotFoundException("Requested template '".$file."' is missing.");
		}

		return $file;
	}



	protected function createTemplate()
	{
		$template = parent::createTemplate();
		$template->setTranslator($this->getTranslator());

		$action = ltrim($this->getAction(TRUE), ':');
		$module = String::lower(substr($action, 0, strpos($action, ':')));
		$theme = Environment::getConfig("theme")->{$module};

		$template->theme = $template->basePath . '/theme_' . $theme;
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



	/*=========================== Components magic =============================*/



	public function createComponent($name)
	{
		$component = parent::createComponent($name);
		return Kdyby\Component\Helpers::createComponent($this, $component, $name);
	}


}