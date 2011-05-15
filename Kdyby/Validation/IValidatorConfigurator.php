<?php

namespace Kdyby\Validation;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
interface IValidatorConfigurator
{

	function configureValidator(Rules $rules);

}