<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Social\Facebook;

use Nette;



/**
 * Dialogs provide a simple, consistent interface to provide social functionality to your users.
 * Dialogs do not require any additional permissions because they require user interaction.
 * Dialogs can be used by your application in every context: within a Canvas Page, in a Page Tab,
 * in a website or mobile web app, and within native iOS and native Android applications.
 *
 * @author Filip Procházka <filip@prochazka.su>
 */
interface Dialog
{

	const DISPLAY_PAGE = 'page';
	const DISPLAY_POPUP = 'popup';
	const DISPLAY_IFRAME = 'iframe';
	const DISPLAY_TOUCH = 'touch';
	const DISPLAY_WAP = 'wap';

	/**
	 * Returns url for the dialog window.
	 *
	 * @param string $display
	 * @param bool $showError
	 * @return string
	 */
	function getUrl($display = self::DISPLAY_POPUP, $showError = FALSE);

	/**
	 * @return Facebook
	 */
	function getFacebook();

}
