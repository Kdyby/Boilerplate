<?php

namespace Kdyby\Gateway;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
interface IService
{

	function getAdapter();

	function getGateways();

}