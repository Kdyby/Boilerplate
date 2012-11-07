<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

$context = new ZMQContext();
$subscriber = $context->getSocket(ZMQ::SOCKET_PULL);
$subscriber->bind("tcp://*:5556");

while ($msg = $subscriber->recv()) {
	echo "$msg\n";
}
