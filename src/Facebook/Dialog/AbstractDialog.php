<?php

namespace Facebook\Dialog;

use Nette;
use Nette\Application\UI\ISignalReceiver;
use Nette\Application\UI\Link;
use Nette\Application\UI\PresenterComponent;
use Nette\Http\UrlScript;
use Facebook;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method onResponse(AbstractDialog $dialog, $response)
 */
abstract class AbstractDialog extends PresenterComponent implements Facebook\Dialog
{

	/**
	 * @var array of function(AbstractDialog $dialog, $response)
	 */
	public $onResponse = array();

	/**
	 * @var \Facebook\Facebook
	 */
	protected $facebook;

	/**
	 * Display mode in which to render the Dialog.
	 * @var string
	 */
	protected $display = self::DISPLAY_POPUP;

	/**
	 * @var UrlScript
	 */
	private $currentUrl;



	/**
	 * @param \Facebook\Facebook $facebook
	 */
	public function __construct(Facebook\Facebook $facebook)
	{
		$this->facebook = $facebook;
		$this->currentUrl = $facebook->getCurrentUrl();

		$this->monitor('Nette\Application\IPresenter');
		parent::__construct();
	}



	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Nette\Application\IPresenter) {
			return;
		}

		$this->currentUrl = new UrlScript($this->link('response!'));
	}



	/***/
	public function getResponse()
	{

	}



	/**
	 *
	 */
	public function handleResponse()
	{
		$this->onResponse($this, $this->getResponse());
	}



	/**
	 * @return array
	 */
	public function getQueryParams()
	{
		$data = array(
			'client_id' => $this->facebook->config->appId,
			'redirect_uri' => (string)$this->currentUrl,
			'show_error' => $this->showError
		);

		if ($this->display !== NULL) {
			$data['display'] = $this->display;
		}

		return $data;
	}



	/**
	 * @return string
	 */
	public function getUrl()
	{
		$url = clone $this->currentUrl;
		$url->appendQuery($this->getQueryParams());
		return (string)$url;
	}



	/**
	 * @param string $display
	 * @param bool $showError
	 */
	public function render($display = null, $showError = false)
	{
		
	}

}
