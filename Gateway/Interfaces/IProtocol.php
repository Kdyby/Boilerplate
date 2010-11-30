<?php

namespace Kdyby\Gateway\Protocol;



interface IProtocol
{

    function getClient();

	function setClient($client);

}
