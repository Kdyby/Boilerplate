<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\AsseticPackage\Presenter;

use Kdyby;
use Kdyby\Package\AsseticPackage\Response\AssetResponse;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AsseticPresenter extends Nette\Object implements Nette\Application\IPresenter
{

	/** @var string */
	private $readFrom;



	/**
	 * @param string $readFrom
	 */
	public function __construct($readFrom)
	{
		$this->readFrom = $readFrom;
	}



	/**
	 * @param \Nette\Application\Request $request
	 *
	 * @return \Nette\Application\IResponse
	 */
	public function run(Nette\Application\Request $request)
	{
		return new AssetResponse(trim(@$request->parameters['path'], '/'));
	}

}
