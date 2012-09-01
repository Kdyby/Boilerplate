<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Social\Facebook;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface ApiClient
{

	/**
	 * Invoke the old restserver.php endpoint.
	 *
	 * @param array $params Method call object
	 * @throws \Kdyby\Extension\Social\Facebook\FacebookApiException
	 * @return mixed The decoded response object
	 */
	function restServer(array $params);

	/**
	 * Invoke the Graph API.
	 *
	 * @param string $path The path (required)
	 * @param string $method The http method (default 'GET')
	 * @param array $params The query/post data
	 * @throws \Kdyby\Extension\Social\Facebook\FacebookApiException
	 * @return mixed The decoded response object
	 */
	function graph($path, $method = 'GET', array $params = array());

	/**
	 * Make a OAuth Request.
	 *
	 * @param string $url The path (required)
	 * @param array $params The query/post data
	 *
	 * @return string The decoded response object
	 * @throws \Kdyby\Extension\Social\Facebook\FacebookApiException
	 */
	function oauth($url, array $params);

}
