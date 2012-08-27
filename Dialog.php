<?php

namespace Kdyby\Extension\Social\Facebook;

use Nette;



/**
 * Dialogs provide a simple, consistent interface to provide social functionality to your users.
 * Dialogs do not require any additional permissions because they require user interaction.
 * Dialogs can be used by your application in every context: within a Canvas Page, in a Page Tab,
 * in a website or mobile web app, and within native iOS and native Android applications.
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
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

}
