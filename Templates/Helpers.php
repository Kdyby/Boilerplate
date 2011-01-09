<?php

namespace Kdyby\Template;

use Nette;
use Kdyby;



class Helpers extends Nette\Object
{


	/**
	 * <code>
	 * Examples for common "app/FrontModule/presenters/HomepagePresenter.php"
	 * ":Front/something" -> "app/FrontModule/templates/something.latte"
	 * ":/something" -> "app/templates/something.latte"
	 * "../something" -> "app/templates/something.latte"
	 * "../../something" -> Exception
	 * "../Client:Setup/something" -> "app/ClientModule/SetupModule/templates/something.latte"
	 * </code>
	 *
	 * @param string $search
	 * @return string
	 */
	public static function searchTemplate(Nette\Application\Presenter $presenter, $search)
	{
		$action = $presenter->getAction(TRUE);
		$ns = explode(':', trim(substr($action, 0, strrpos($action, ':')), ':'));

		if (substr_count($search, '/') > 0) {
			$ex = (int)strrpos($search, '/');
			$nettePath = substr($search, 0, $ex);
			$path = substr($search, ($ex>0 ? $ex+1 : 0));

			if (substr($nettePath, 0, 1) === ':') {
				// absolute ":Front/something.latte"
				// absolute ":/something.latte"

				$ns = array_filter(
						String::split(trim($nettePath, ':/'), '~:~'),
						function($v){ return (bool)$v; }
					);
				$file = $path;

			} elseif (substr($nettePath, 0, 3) === '../') {
				// relative "../@layout.latte"
				// relative "../../something.latte"
				// relative "../Client:Setup/something.latte"

				while (substr($nettePath, 0, 3) == '../') {
					if (count($ns) === 0) {
						throw new \InvalidArgumentException("Error in search query '".$search."', are you trying to jump out of app dir? Sorry, can't do that.");
					}

					array_pop($ns);
					$nettePath = substr($nettePath, 3);
				}

				$relativePath = array_filter(
						String::split(trim($nettePath, ':/'), '~:~'),
						function($v){ return (bool)$v; }
					);
				//dump($ns, $relativePath);die();
				$ns = array_merge((array)$ns, $relativePath);
				$module = ($ns ? "\\". implode("Module\\", $ns).'Module' : NULL);
				$file = $path;
			}

		} else {
			$file = $search;
		}

		$file = APP_DIR . '/' . // app dir
			($ns ? implode('Module/', $ns) . 'Module/' : NULL) . 'templates/' . // path to templates dir
			$file . '.latte'; // filename

		if (!file_exists($file)) {
			if (file_exists(substr($file, 0, -5).'phtml')) { // depracated
				throw new \FileNotFoundException("Requested template '".substr($file, 0, -5)."phtml' should be using '.latte' extension.");
			}

			throw new \FileNotFoundException("Requested template '".$file."' is missing.");
		}

		return $file;
	}



	/**
	 * @return string
	 */
	public static function getBaseUri()
	{
		return rtrim(Nette\Environment::getVariable('baseUri', NULL), '/');
	}



	/**
	 * @return string
	 */
	public static function getBasePath()
	{
		return preg_replace('#https?://[^/]+#A', '', self::getBaseUri());
	}



	/**
	 * @param Nette\Application\Presenter $presenter
	 * @param string $switch
	 * @return string
	 */
	public static function getThemePath(Nette\Application\Presenter $presenter, $switch = NULL)
	{
		if ($switch) {
			return self::getBasePath() . '/_' . $switch;
		}

		// nette-links helper
		// TODO: save somewhere!
		$trim = function($link, $right = TRUE){
			return $right ? substr($link, 0, strrpos($link, ':')) : substr($link, 0, strpos($link, ':'));
		};

		$action = ltrim($presenter->getAction(TRUE), ':');
		$presenter = $trim($action);
		$module = $trim($presenter);
		$modules = explode(':', $module);

		// save theese
		$rootModule = Nette\String::lower($trim($action, FALSE));
		$themes = Nette\Environment::getConfig("theme");

		return self::getBasePath() . '/_' . $themes->{$rootModule};
	}

}