<?php

namespace Kdyby\Templates;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class FileTemplate extends Nette\Templating\FileTemplate
{

	/**
	 * @param array $params
	 */
	public function addParams(array $params)
	{
		$this->setParams($params + $this->getParams());
	}

}