<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Templates;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MacrosAnalyzer extends Nette\Object
{

	/** @var array */
	private $macros = array();

	/** @var \Nette\Templating\Template */
	private $template;

	/** @var \Kdyby\Templates\AnalyzerMacroSet */
	private $analyzingMacros;



	/**
	 * @param Latte\Engine|null $engine
	 */
	public function __construct(Latte\Engine $engine = NULL)
	{
		$engine = $engine ? : new Nette\Latte\Engine;
		$this->template = new Nette\Templating\Template();
		$this->template->registerFilter($engine);

		$this->analyzingMacros = AnalyzerMacroSet::install($engine->parser);
	}



	/**
	 * @param string $templateSource
	 */
	public function analyze($templateSource)
	{
		$this->template->setSource($templateSource);
		$this->template->compile();

		$this->macros = $this->analyzingMacros->getResults();
	}



	/**
	 * @param string $type
	 * @return \Nette\Latte\MacroNode
	 */
	public function getMacros($type = NULL)
	{
		if ($type && !isset($this->macros[$type])) {
			return array();
		}

		return $type ? $this->macros[$type] : $this->macros;
	}

}
