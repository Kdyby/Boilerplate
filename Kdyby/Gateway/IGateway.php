<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Gateway;

use Kdyby;



interface IGateway
{

	public function __construct(Protocols\IProtocol $protocol);

    function createRequest();

	function openRequest(IRequest $request);

	function createResponse($result);

}
