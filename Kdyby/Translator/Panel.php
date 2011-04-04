<?php
/*
 * Copyright (c) 2010 Jan Smitka <jan@smitka.org>
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Kdyby\Translator;

use Nette\Environment;



/**
 * Panel for Nette DebugBar, which enables you to translate strings
 * directly from your browser.
 *
 * @author Jan Smitka <jan@smitka.org>
 * @author Patrik Votoček <patrik@votocek.cz>
 */
class Panel implements \Nette\IDebugPanel
{
	const XHR_HEADER = "X-Translation-Client";
	const SESSION_NAMESPACE = "NetteTranslator-Panel";
	/* Layout constants */
	const LAYOUT_HORIZONTAL = 1;
	const LAYOUT_VERTICAL = 2;

	/** @var int TranslationPanel layout */
	protected $layout = self::LAYOUT_VERTICAL;

	/** @var int Height of the editor */
	protected $height = 350;



	public function __construct($layout = NULL, $height = NULL)
	{
		if ($height !== NULL) {
			if (!is_numeric($height))
				throw new \InvalidArgumentException('Panel height has to be a numeric value.');
			$this->height = $height;
		}

		if ($layout !== NULL) {
			$this->layout = $layout;
			if ($height === NULL)
				$this->height = 500;
		}

		$this->processRequest();
	}



	/**
	 * Return's panel ID.
	 * @return string
	 */
	public function getId()
	{
		return 'translation-panel';
	}



	/**
	 * Returns the code for the panel tab.
	 * @return string
	 */
	public function getTab()
	{
		ob_start();
		require __DIR__ . '/tab.phtml';
		return ob_get_clean();
	}



	/**
	 * Returns the code for the panel itself.
	 * @return string
	 */
	public function getPanel()
	{
		$translator = Environment::getService('Nette\ITranslator');
		$strings = $translator->getStrings();

		if (Environment::getSession()->isStarted()) {
			$session = Environment::getSession(self::SESSION_NAMESPACE);
			$untranslatedStack = isset($session['stack']) ? $session['stack'] : array();
			foreach ($strings as $string => $data) {
				if (!$data) {
					$untranslatedStack[$string] = FALSE;
				}
			}
			$session['stack'] = $untranslatedStack;

			foreach ($untranslatedStack as $string => $value) {
				if (!isset($strings[$string]))
					$strings[$string] = FALSE;
			}
		}

		ob_start();
		require __DIR__ . '/panel.phtml';
		return ob_get_clean();
	}



	/**
	 * Handles an incomuing request and saves the data if necessary.
	 */
	private function processRequest()
	{
		// Try starting the session
		try {
			$session = Environment::getSession(self::SESSION_NAMESPACE);
		} catch (\InvalidStateException $e) {
			$session = FALSE;
		}

		$request = Environment::getHttpRequest();
		if ($request->isPost() && $request->isAjax() && $request->getHeader(self::XHR_HEADER)) {
			$data = json_decode(file_get_contents('php://input'));
			$translator = Environment::getService('Nette\ITranslator');

			if ($data) {
				if ($session) {
					$stack = isset($session['stack']) ? $session['stack'] : array();
				}

				$translator->lang = $data->{'x-nette-translationpanel-lang'};
				unset($data->{'x-nette-translationpanel-lang'});

				foreach ($data as $string => $value) {
					$translator->setTranslation($string, $value);
					if ($session && isset($stack[$string]))
						unset($stack[$string]);
				}
				$translator->save();

				if ($session)
					$session['stack'] = $stack;
			}
			exit;
		}
	}



	/**
	 * Return an odrdinal number suffix.
	 * @param string $count
	 * @return string
	 */
	protected function ordinalSuffix($count)
	{
		switch (substr($count, -1)) {
			case '1':
				return 'st';
			case '2':
				return 'nd';
			case '3':
				return 'rd';
			default:
				return 'th';
		}
	}



	/**
	 * Register this panel
	 *
	 * @param NetteTranslator\IEditable $translator
	 * @param int $layout
	 * @param int $height
	 */
	public static function register(IEditable $translator = NULL, $layout = NULL, $height = NULL)
	{
		\Nette\Debug::addPanel(new static($layout, $height));
	}

}