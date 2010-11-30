<?php

namespace Kdyby\Gateway;



interface IGateway
{

	public function __construct(Protocol\IProtocol $protocol);

    function createRequest();

	function openRequest();

	function createResponse($result);

}
