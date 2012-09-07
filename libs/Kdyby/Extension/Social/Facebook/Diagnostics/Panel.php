<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Social\Facebook\Diagnostics;

use Kdyby\Extension\Social\Facebook;
use Nette;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Html;
use Nette\Utils\Json;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Panel extends Nette\Object implements Nette\Diagnostics\IBarPanel
{

	/**
	 * @var int logged time
	 */
	private $totalTime = 0;

	/**
	 * @var array
	 */
	private $calls = array();

	/**
	 * @var \stdClass
	 */
	private $current;



	/**
	 * @return string
	 */
	public function getTab()
	{
		$img = Html::el('img')->src('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAABW0lEQVR42qWS3ytDYRjH9z+R2SEucCFxYdkOo6HQkanphCtFlvy4XTtna360WjaUcJy5Ie1iKaVQftVakXaxaEmp2fl633dxDsu52VOfm+f9fs77PG/HUlHVdcspgkbAX2y8BGtXADUEjpc0mtVFXSqjQ4jAu6BAXDxE28gGk3WxXCABGa1D6zg4ucV3TS4n2M2mos0pwSXGUSgUcXHzjJVwEnZPlPbNxfqeINzT26AVV69Q1ekHVzr7X2zoDSF5nsFTNs/E/NsH0o8vGJ3dg9URMBFdQSSS93jI5JiYe33H5V0WwzO7qKWi+agy3FOlUXeOruludPzfo3LsFSUj7MsuMcbELbJjtd1fOjOKLYNrcE7EwBtweDchLqlMVMnY9vEoy5CsLrYLEc3jU2BkbF6BTz5F4bOI47M0hLl91qfZH7GxL5RqHljVCDBAfwC6J7utqZ/1NJq1VFJfEBZqgX+geF0AAAAASUVORK5CYII=');
		$tab = Html::el('span')->title('Facebook')->add($img);
		$title = Html::el('strong')->setText('Facebook');
		if ($this->calls) {
			$title->setText(
				count($this->calls) . ' call' . (count($this->calls) > 1 ? 's' : '') .
				' / ' . sprintf('%0.2f', $this->totalTime) . ' s'
			);
		}
		return (string)$tab->add($title);
	}



	/**
	 * @return string
	 */
	public function getPanel()
	{
		if (!$this->calls) {
			return;
		}

		ob_start();
		$esc = callback('Nette\Templating\Helpers::escapeHtml');
		$click = callback('Nette\Diagnostics\Helpers::clickableDump');
		$totalTime = $this->totalTime ? sprintf('%0.3f', $this->totalTime * 1000) . ' ms' : 'none';

		require_once __DIR__ .'/panel.phtml';
		return ob_get_clean();
	}



	/**
	 * @param string|object $url
	 * @param array $params
	 */
	public function begin($url, array $params)
	{
		if ($this->current) return;
		$this->calls[] = $this->current = (object)array(
			'url' => $url,
			'params' => $params,
			'result' => NULL,
			'exception' => NULL,
			'info' => array(),
			'time' => 0,
		);
	}



	/**
	 * @param mixed $result
	 * @param array $curlInfo
	 */
	public function success($result, array $curlInfo)
	{
		if (!$this->current) return;
		$this->totalTime += $this->current->time = $curlInfo['total_time'];
		unset($curlInfo['total_time']);
		$this->current->info = $curlInfo;

		try {
			$result = Nette\Utils\Json::decode($result);

		} catch (Nette\Utils\JsonException $e) {
			@parse_str($result, $params);
			$result = !empty($params) ? $params : $result;
		}

		$this->current->result = $result;

		$this->current = NULL;
	}



	/**
	 * @param \Facebook\FacebookApiException $exception
	 * @param array $curlInfo
	 */
	public function failure(Facebook\FacebookApiException $exception, array $curlInfo)
	{
		if (!$this->current) return;

		$this->totalTime += $this->current->time = $curlInfo['total_time'];
		unset($curlInfo['total_time']);
		$this->current->info = $curlInfo;
		$this->current->exception = $exception;

		$this->current = NULL;
	}



	/**
	 * Register into BarPanel.
	 */
	public function register()
	{
		Debugger::$bar->addPanel($this);
	}


}
