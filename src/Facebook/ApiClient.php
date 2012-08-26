<?php

namespace Facebook;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface ApiClient
{

	/**
	 * Invoke the old restserver.php endpoint.
	 *
	 * @param array $params Method call object
	 * @throws \Facebook\FacebookApiException
	 * @return mixed The decoded response object
	 */
	function restServer(array $params);



	/**
	 * Invoke the Graph API.
	 *
	 * @param string $path The path (required)
	 * @param string $method The http method (default 'GET')
	 * @param array $params The query/post data
	 * @throws \Facebook\FacebookApiException
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
	 * @throws \Facebook\FacebookApiException
	 */
	function oauth($url, array $params);

}
