<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Nette;
use Nette\Latte\Engine;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class LatteTemplateOutput extends Nette\Object
{

	/** @var string */
	public $prolog;

	/** @var string */
	public $epilog;

	/** @var string */
	public $macro;

	/** @var \Nette\Latte\Engine */
	private $latte;



	/**
	 * @param \Nette\Latte\Engine $engine
	 */
	public function __construct($engine)
	{
		$this->latte = $engine;
		$this->prolog = array();
		$this->macro = array();
		$this->epilog = array();
	}



	/**
	 * @param string $source
	 *
	 * @return \Kdyby\Tests\Tools\LatteTemplateOutput
	 */
	public function parse($source)
	{
		$template = new Nette\Templating\Template();
		$template->registerFilter($this->latte);
		$template->setSource($source);
		$output = $template->compile();

		$lines = array_filter(explode("\n", $output), function ($line) {
			return $line !== '//';
		});
		$part = NULL;
		foreach ($lines as $line) {
			if (strpos($line, '// prolog') === 0) {
				$part = 'prolog';
				continue;

			} elseif (strpos($line, '// main template') === 0) {
				$part = 'macro';
				continue;

			} elseif (strpos($line, '// epilog') === 0) {
				$part = 'epilog';
				continue;
			}

			if ($part !== NULL) {
				$this->{$part}[] = $line;
			}
		}

		$this->prolog = implode("\n", $this->prolog);
		$this->macro = implode("\n", $this->macro);
		$this->epilog = implode("\n", $this->epilog);

		return $this;
	}

}
