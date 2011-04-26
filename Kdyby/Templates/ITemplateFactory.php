<?php

namespace Kdyby\Templates;

use Kdyby;
use Nette;



interface ITemplateFactory
{

	/**
	 * @param Nette\ComponentModel\Component $component
	 * @return Nette\Templating\ITemplate
	 */
	function createTemplate(Nette\ComponentModel\Component $component);

}