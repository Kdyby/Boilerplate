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
use Nette\Application\UI;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class UIFormTestingPresenter extends UI\Presenter
{

	/** @var \Nette\Application\UI\Form */
	private $form;



	/**
	 * @param \Nette\Application\UI\Form $form
	 */
	public function __construct(UI\Form $form)
	{
		parent::__construct();
		$this->form = $form;
	}



	/**
	 * Just terminate the rendering
	 */
	public function renderDefault()
	{
		$this->terminate();
	}



	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentForm()
	{
		return $this->form;
	}

}
