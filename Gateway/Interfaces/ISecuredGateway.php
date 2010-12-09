<?php

namespace Kdyby\Gateway;

use Kdyby;



interface ISecuredGateway extends IGateway
{

    function authenticate(Kdyby\Gateway\ISecuredRequest $request);

	function getAuthenticationHandler();

}
