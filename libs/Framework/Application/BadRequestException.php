<?php

namespace Kdyby\Application;

use Nette;



class BadRequestException extends Nette\Application\BadRequestException
{

	public static function notAllowed()
	{
		return new static("You're not allowed to see this page.", 403);
	}

	public static function nonExisting()
	{
		return new static("This page does not realy exists.", 404);
	}

}