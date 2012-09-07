<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\EventDispatcher;

use Doctrine;
use Kdyby;
use Nette;



/**
 * An EventSubscriber knows himself what events he is interested in.
 * If an EventSubscriber is added to an EventManager, the manager invokes
 * {@link getSubscribedEvents} and registers the subscriber as a listener for all
 * returned events.
 *
 * @author Filip Procházka <filip@prochazka.su>
 */
interface EventSubscriber extends Doctrine\Common\EventSubscriber
{

}
