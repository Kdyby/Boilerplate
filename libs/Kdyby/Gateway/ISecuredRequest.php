<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Gateway;

use Kdyby;



/**
 * @author Filip Procházka
 */
interface ISecuredRequest extends IRequest
{

    function setAuthentication(IGatewayAuthenticator $handler);

}
