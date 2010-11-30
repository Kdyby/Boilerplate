<?php

namespace Kdyby\Gateway;



interface IResponse
{

	function __construct($raw);

    function getRawResponse();

}
