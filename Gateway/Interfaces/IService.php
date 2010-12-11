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



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
interface IService
{

	function getAdapter();

	function getGateways();

}