<?php

namespace Kdyby\Security;

use Nette;
use Kdyby;


/**
 * Globální zámek pro aplikaci, vyžaduje auth handler, ideálně napojit http_authentication
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class ApplicationLock extends Nette\Object
{

	/** @var array */
	private $users;

	/** @var Nette\Web\HttpRequest */
	private $httpRequest;

	/** @var string */
	public $message = "Access denied";

	/** @var string */
	public $realm = "test";



	public function __construct()
	{
		$this->httpRequest = Nette\Environment::getHttpRequest();
	}



	/**
	 * @param string $name
	 * @param string $pass 
	 */
	public function addUser($name, $pass)
	{
		$this->users[$name] = $pass;
	}



	/**
	 * @return Kdyby\Security\ApplicationLock
	 */
	public function authorize()
	{
		$uri = $this->httpRequest->getUri();

		if (!isset($uri->user) || !isset($uri->password)) {
			$this->kill();
		}

		$user = $uri->user;
		$pass = $uri->password;

		if (isset($this->users[$user]) && $this->users[$user] === $pass) {
			header('Content-Type: text/html; charset=utf-8');
			return $this;
		}

		return $this->kill();
	}



	private function kill()
	{
		header('Content-Type: text/plain; charset=utf-8');
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Basic realm="'.(string)$this->realm.'"');
		die((string)$this->message);
	}



	/**
	 * @param array $options
	 * @return Kdyby\Security\ApplicationLock
	 */
	public static function createApplicationLock($options)
	{
		$lock = new self;

		foreach ((array)$options['user'] as $name => $pass) {
			//list($name, $pass) = explode('=', $user);
			$lock->addUser($name, $pass);
		}

		$lock->message = isset($options['message']) ? $options['message'] : $lock->message;
		$lock->realm = isset($options['realm']) ? $options['realm'] : $lock->realm;

		return $lock->authorize();
	}

}