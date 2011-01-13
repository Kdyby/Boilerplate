<?php

namespace Kdyby\Template;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class FileTemplate extends Nette\Templates\FileTemplate
{

	/**
	 * @param array $params
	 */
	public function addParams(array $params)
	{
		$this->setParams($params + $this->getParams());
	}

}