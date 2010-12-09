<?php

namespace Kdyby\Gateway;

use Kdyby;



interface IGateway
{

	public function __construct(Kdyby\Gateway\Protocol\IProtocol $protocol);

    function createRequest();

	function openRequest(Kdyby\Gateway\IRequest $request);

	function createResponse($result);

}
