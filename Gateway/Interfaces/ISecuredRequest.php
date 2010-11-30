<?php

namespace Kdyby\Gateway;



interface ISecuredRequest extends IRequest
{

    function setAuthentication(Kdyby\Gateway\IGatewayAuthenticator $handler);

}
