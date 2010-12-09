<?php

namespace Kdyby\Gateway;

use Kdyby;



interface ISecuredRequest extends IRequest
{

    function setAuthentication(Kdyby\Gateway\IGatewayAuthenticator $handler);

}
