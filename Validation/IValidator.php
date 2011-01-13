<?php

namespace Kdyby\Validation;

use Kdyby;
use Nette;
use Doctrine;



interface IValidator
{

	function validate($entity);

}