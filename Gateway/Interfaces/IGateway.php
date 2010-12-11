<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Gateway;

use Kdyby;



interface IGateway
{

	public function __construct(Kdyby\Gateway\Protocol\IProtocol $protocol);

    function createRequest();

	function openRequest(Kdyby\Gateway\IRequest $request);

	function createResponse($result);

}
