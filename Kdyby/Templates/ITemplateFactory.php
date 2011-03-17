<?php

namespace Kdyby\Templates;

use Kdyby;
use Nette;



interface ITemplateFactory
{

	/**
	 * @param Nette\Component $component
	 * @return Nette\Templates\ITemplate
	 */
	function createTemplate(Nette\Component $component);

}