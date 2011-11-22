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
 *
 * Entity ?
 */
class Theme extends Nette\Object implements ITheme
{

	/** @var string */
	public $name;

	/** @var string */
	public $path;



	/**
	 * @param Kdyby\DI\Container $context
	 */
	public function __construct(Kdyby\DI\Container $context)
	{
		$context->params['themePath'] = $this->path;
	}



	/**
	 * @param Nette\Templating\ITemplate $template
	 */
	public function setupTemplate(Nette\Templating\ITemplate $template)
	{
		$template->themePath = $this->path;
	}



	/**
	 * @param Nette\Latte\Parser $parser
	 */
	public function installMacros(Nette\Latte\Parser $parser)
	{

	}

}