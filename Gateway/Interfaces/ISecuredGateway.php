<?php

namespace Kdyby\Gateway;


interface ISecuredGateway extends IGateway
{

    function authenticate(Kdyby\Gateway\ISecuredRequest $request);

	function getAuthenticationHandler();

}
