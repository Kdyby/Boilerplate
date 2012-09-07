/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */


/**
 * @author Filip Procházka <filip@prochazka.su>
 */
(function($, undefined) {

	/**
	 */
	$.nette.ext('codeEditor', {
		before: function (settings, ui) {
			if (ui) {
				var $editors = $(ui).closest('form').find('textarea.code-editor');
				$editors.each(function () {
					var $editor = $(this);
					var codeEditor = $editor.data('code-editor');
					if (codeEditor) {
						$editor.val(codeEditor.getValue());
						settings.data[$editor.attr('name')] = $editor.val();
					}
				});
			}
		}
	});

})(jQuery);
