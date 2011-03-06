<?php

namespace KdybyTests\Reflection\Mocks;

use Nette;
use Kdyby;



class WirableDummyService extends Nette\Object
{

	public function __construct(Nette\IContext $context, Nette\Web\IHttpRequest $httpRequest)
	{
		
	}

}