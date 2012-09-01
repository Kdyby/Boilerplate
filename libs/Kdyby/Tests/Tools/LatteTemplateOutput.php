<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Nette;
use Nette\Latte\Engine;



/**
 * @author Filip Procházka <filip@prochazka.su>
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

	/** @var string */
	private $tempDir;



	/**
	 * @param \Nette\Latte\Engine $engine
	 */
	public function __construct($engine, $tempDir)
	{
		$this->latte = $engine;
		$this->tempDir = $tempDir;
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

		try {
			$output = $template->compile();

		} catch (Nette\Latte\CompileException $e) {
			$tmpFile = $this->tempDir . '/' . md5($source) . '.latte';
			file_put_contents($tmpFile, $source);
			$e->setSourceFile($tmpFile);
			$this->epilog = $this->macro = $this->prolog = NULL;
			throw $e;
		}

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

		if (!$this->prolog && !$this->macro && !$this->epilog) {
			$this->macro = $output;

		} else {
			$this->prolog = implode("\n", $this->prolog);
			$this->macro = implode("\n", $this->macro);
			$this->epilog = implode("\n", $this->epilog);
		}

		return $this;
	}

}
