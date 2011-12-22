<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Diagnostics;

use Kdyby;
use Nette;
use Nette\Application\UI;
use Nette\Diagnostics\Debugger;
use Nette\Reflection\Method;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TemplateParametersPanel extends Nette\Object implements Nette\Diagnostics\IBarPanel
{

	/** @var array */
	private $components = array();



	/**
	 * @param \Nette\Application\UI\PresenterComponent $component
	 */
	public function addComponent(UI\PresenterComponent $component)
	{
		if (!$component->getReflection()->hasMethod('getTemplate')) {
			return;
		}

		$template = $component->getTemplate();
		if (!$template instanceof \Nette\Templating\Template) {
			return;
		}

		$this->components[$component->getUniqueId() ? : 'presenter'] = $template;
	}



	/**
	 * Renders HTML code for custom tab.
	 * @return string
	 */
	public function getTab()
	{
		if (!$this->components) {
			return NULL;
		}

		$img = file_get_contents(__DIR__ . '/templates/bar.templateparams.tab.phtml');
		return $img . 'templates';
	}



	/**
	 * Renders HTML code for custom panel.
	 * @return string
	 */
	public function getPanel()
	{
		ob_start();
		$data = $this->getComponents();
		require __DIR__ . '/templates/bar.templateparams.panel.phtml';
		return ob_get_clean();
	}



	/**
	 * @return array
	 */
	private function getComponents()
	{
		$dump = array();
		foreach ($this->components as $name => $component) {
			foreach ($component->getTemplate()->getParameters() as $key => $val) {
				$dump[$name][$key] = Nette\Diagnostics\Helpers::clickableDump($val);
			}
		}

		return $dump;
	}



	/**
	 * @param \Nette\Application\UI\PresenterComponent $component
	 */
	public static function register(UI\PresenterComponent $component)
	{
		if (!Debugger::isEnabled()) {
			return;
		}

		$panel = new static();
		$panel->addComponent($component);
		foreach ($component->getComponents(TRUE, 'Nette\Application\UI\PresenterComponent') as $child) {
			$panel->addComponent($child);
		}

		Debugger::$bar->addPanel($panel);
	}

}
