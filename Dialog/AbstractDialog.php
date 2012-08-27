<?php

namespace Kdyby\Extension\Social\Facebook\Dialog;

use Kdyby\Extension\Social\Facebook;
use Nette;
use Nette\Application\UI\PresenterComponent;
use Nette\Http\UrlScript;
use Nette\Utils\Html;



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
	protected $display;

	/**
	 * @var bool
	 */
	protected $showError;

	/**
	 * @var UrlScript
	 */
	protected $currentUrl;



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

		$this->currentUrl = new UrlScript($this->link('//response!'));
	}



	/**
	 * @return bool
	 */
	public function getResponse()
	{
		return TRUE;
	}



	/**
	 *
	 */
	public function handleResponse()
	{
		if ($this->onResponse) {
			$this->onResponse($this, $this->getResponse());
		}
		// $this->presenter->redirect('this');
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
	 * @param string $display
	 * @param bool $showError
	 *
	 * @return string
	 */
	public function getUrl($display = self::DISPLAY_POPUP, $showError = FALSE)
	{
		$url = clone $this->currentUrl;

		$this->display = $display;
		$this->showError = $showError;

		$url->appendQuery($this->getQueryParams());
		return (string)$url;
	}



	/**
	 * @param string $display
	 * @param bool $showError
	 * @return Html
	 */
	public function getControl($display = self::DISPLAY_POPUP, $showError = FALSE)
	{
		return Html::el('a')->url($this->getUrl($display, $showError));
	}



	/**
	 * @param string $title
	 * @param string $display
	 * @param bool $showError
	 */
	public function render($title = "click me!", $display = self::DISPLAY_POPUP, $showError = FALSE)
	{
		echo $this->getControl($display, $showError)
			->setText($title)
			->target('_blank');
	}

}
