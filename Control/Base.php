<?php

namespace Kdyby\Control;

use Kdyby;
use Nette;
use Nette\Environment;
use Nette\String;



/**
 * Description of Base
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Base extends Nette\Application\Control
{

	public function getUser()
	{
		return Nette\Environment::getUser();
	}



	/*=========================== Templates =============================*/



	protected function createTemplate()
	{
		$template = parent::createTemplate();
		$template->setTranslator(Environment::getService('Nette\ITranslator'));

		$action = ltrim($this->presenter->getAction(TRUE), ':');
		$module = String::lower(substr($action, 0, strpos($action, ':')));
		$theme = Environment::getConfig("theme")->{$module};

		$template->theme = Environment::getVariable('baseUri') . 'theme_' . $theme;

		return $template;
	}


	public function templatePrepareFilters($template)
	{
		$this->presenter->templatePrepareFilters($template);
	}

}
