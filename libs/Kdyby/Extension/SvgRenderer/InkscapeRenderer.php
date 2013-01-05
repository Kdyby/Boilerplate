<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\SvgRenderer;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class InkscapeRenderer extends Nette\Object implements IRenderer
{

	/**
	 * @param DI\Configuration $config
	 */
	public function __construct(DI\Configuration $config)
	{
		// pass
	}



	/**
	 * @param SvgImage $svg
	 * @throws ProcessException
	 * @return string
	 */
	public function render(SvgImage $svg)
	{
		$process = new InkscapeProcess($this->buildOptions($svg));
		return $process->execute();
	}



	/**
	 * @param SvgImage $svg
	 * @throws IOException
	 * @return array
	 */
	private function buildOptions(SvgImage $svg)
	{
		return array(
			'' => $svg->getString()
		);
	}

}
