<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * This source file is subject to the "Kdyby license", and/or
 * GPL license. For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


use Nette\Debug;
use Nette\Environment;


@header('X-Generated-By: Kdyby CMF ;url=www.kdyby.org'); // @ - headers may be sent

define('KDYBY_DIR', __DIR__);

// first think to do is load Kdyby libs
require KDYBY_DIR . '/libs/loader.php';


