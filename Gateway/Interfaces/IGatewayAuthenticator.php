<?php

namespace Kdyby\Gateway;



interface IGatewayAuthenticator
{

	function authenticate();

	function isAuthenticated();

}
