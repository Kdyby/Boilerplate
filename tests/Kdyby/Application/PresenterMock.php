<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace KdybyTests\Application;

use Kdyby;



class PresenterMock extends Kdyby\Application\Presenter
{
	public function createComponentMock($name)
	{
		return $this->createComponent($name);
	}
}

namespace Kdyby;

class MyPresenter extends \Kdyby\Application\Presenter { }

namespace Kdyby\FooModule;

class MyPresenter extends \Kdyby\Application\Presenter { }

namespace App;

class FooPresenter extends \Kdyby\Application\Presenter { }
abstract class BarPresenter extends \Kdyby\Application\Presenter { }
class BazPresenter { }

namespace App\BarModule;

class FooPresenter extends \Kdyby\Application\Presenter { }