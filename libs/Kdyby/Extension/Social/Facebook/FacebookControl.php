<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Social\Facebook;

use Nette;
use Nette\Caching\Cache;
use Nette\Caching\Storages\MemoryStorage;



/**
 * This component is an interlink between lifecycle of Nette presenters & Facebook API
 *
 * <code>
 * $fb = $facebook->createControl();
 * $fb->on['login']['response'] = function (Fb\Dialog\LoginDialog $dialog) {
 * 	$me = $dialog->getFacebook()->api('/me');
 * 	$dialog->presenter->flashMessage("Hi " . $me['first_name'] . '!');
 * };
 * $fb->on['logout']['response'] = function (Fb\Dialog\LogoutDialog $dialog) {
 * 	$dialog->presenter->flashMessage("Come back soon!");
 * };
 * </code>
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FacebookControl extends Nette\Application\UI\PresenterComponent
{

	/**
	 * @var Facebook
	 */
	private $facebook;

	/**
	 * @var \Nette\Caching\Cache
	 */
	private $cache;

	/**
	 * @var array of callbacks for components
	 */
	public $on = array(
		'login' => array('response' => array()),
		'logout' => array('response' => array()),
		'loginStatus' => array(
			'response' => array(),
			'noSession' => array(),
			'noUser' => array(),
			'okSession' => array()
		),
	);



	/**
	 * @param Facebook $facebook
	 * @param \Nette\Caching\IStorage $cacheStorage
	 */
	public function __construct(Facebook $facebook, Nette\Caching\IStorage $cacheStorage = NULL)
	{
		parent::__construct();
		$this->facebook = $facebook;
		$this->cache = new Cache($cacheStorage ?: new MemoryStorage(), 'Facebook.Static');
	}



	/**
	 * @todo: cache
	 *
	 * @param string $id
	 * @throws \Nette\Application\AbortException
	 */
	public function handleProfilePicture($id)
	{
		$fb = $this->facebook;
		$presenter = $this;

		// fetch public url
		$url = $this->cache->load('profile.picture.' . $id, function (&$dp) use ($fb, $id, $presenter) {
			$db[Cache::EXPIRE] = '+10 minutes';

			/** @var Facebook $fb */
			if (!$url = $fb->getProfile($id)->getPictureUrl()) {
				/** @var \Nette\Application\UI\Presenter $presenter */
				$presenter->error("Picture not found");
			}

			return $url;
		});

		$this->presenter->session->close();

		// fetch image contents
		$picture = $this->cache->load($url, function (&$db) use ($url) {
			$db[Cache::EXPIRE] = '+10 minutes';
			return file_get_contents($url);
		});

		$httpResponse = new Nette\Http\Response();
		$httpResponse->setContentType(Nette\Utils\MimeTypeDetector::fromString($picture));
		$httpResponse->setExpiration('+10 minutes');
		echo $picture;

		throw new Nette\Application\AbortException;
	}



	/**
	 * @param string $dialog
	 */
	public function handleOpen($dialog)
	{
		try {
			$this->presenter->redirectUrl($this[$dialog]->getUrl());

		} catch (InvalidArgumentException $e) {
			$this->presenter->error($e->getMessage());
		}
	}



	/**
	 * @param string $name
	 * @return Dialog|\Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name)
	{
		$dialog = $this->facebook->createDialog($name);
		if (isset($this->on[$name])) {
			foreach ($this->on[$name] as $event => $callbacks) {
				$dialog->{'on' . ucfirst($event)} = is_array($callbacks) ? $callbacks : array($callbacks);
			}
		}
		return $dialog;
	}



	/**
	 * @param Profile $profile
	 * @return string
	 */
	public function profilePicture(Profile $profile)
	{
		return $this->link('profilePicture!', array('id' => $profile->id));
	}

}
