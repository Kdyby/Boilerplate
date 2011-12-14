<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Templates;

use Kdyby;
use Kdyby\Templates\MacrosAnalyzer;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MacrosAnalyzerTest extends Kdyby\Tests\TestCase
{

	/** @var \Kdyby\Templates\MacrosAnalyzer */
	private $analyzer;



	public function setUp()
	{
		$this->analyzer = new MacrosAnalyzer();
	}



	public function testAnalyze()
	{
		$source = file_get_contents(__DIR__ . '/MacrosAnalyzer.template.latte');
		$this->analyzer->analyze($source);

		$this->assertCount(2, $this->analyzer->getMacros('block'));
		$this->assertCount(1, $this->analyzer->getMacros('include'));
		$this->assertCount(1, $this->analyzer->getMacros('foreach'));
		$this->assertCount(1, $this->analyzer->getMacros('link'));
	}

}
