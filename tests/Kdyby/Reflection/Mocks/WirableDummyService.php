<?php

namespace KdybyTests\Reflection\Mocks;

use Nette;
use Kdyby;



class WirableDummyService extends Nette\Object
{

	public function __construct(Nette\DI\IContext $context, Nette\Http\IRequest $httpRequest)
	{
		
	}

}