/**
 * AJAX Nette Framwork plugin for jQuery
 *
 * @copyright   Copyright (c) 2009 Jan Marek
 * @license     MIT
 * @link        http://nettephp.com/cs/extras/jquery-ajax
 * @version     0.2
 */

jQuery.extend({
	nette: {
		updateSnippet: function (id, html) {
			$("#" + id).html(html);
		},

		success: function (payload) {
			// redirect
			if (payload.redirect) {
				window.location.href = payload.redirect;
				return;
			}

			// snippets
			if (payload.snippets) {
				for (var i in payload.snippets) {
					jQuery.nette.updateSnippet(i, payload.snippets[i]);
				}
			}
		}
	}
});

jQuery.ajaxSetup({
	success: jQuery.nette.success,
	dataType: "json"
});



/**
 * AJAX form plugin for jQuery
 *
 * @copyright  Copyright (c) 2009 Jan Marek
 * @license    MIT
 * @link       http://nettephp.com/cs/extras/ajax-form
 * @version    0.1
 */

jQuery.fn.extend({
	serializeValues: function () {
		if (!this.is("form")) {
			return null;
		}
		var sendValues = {};

		// get values
		var values = this.serializeArray();

		for (var i = 0; i < values.length; i++) {
			var name = values[i].name;

			// multi
			if (name in sendValues) {
				var val = sendValues[name];
				if (!(val instanceof Array)) {
					val = [val];
				}

				val.push(values[i].value);
				sendValues[name] = val;
			} else {
				sendValues[name] = values[i].value;
			}
		}

		return sendValues;
	},
	ajaxSubmit: function (options) {
		if (isFunction(options)) {
			options = {
				success: options
			};
		}
		options = options || {};

		var form = (function (form) {
			if (form.is(':submit')) {
				return form.parents("form"); // submit button

			} else if (form.is("form")) {
				return form; // form
			}

			return null; // invalid element, do nothing
		})(this);

		if (form === null) {
			return this; // invalid element, do nothing
		}

		// validation
		if (form.get(0).onsubmit && !form.get(0).onsubmit()) return null;

		// get values
		var sendValues = form.serializeValues();
		if (this.is(":submit")) {
			sendValues[this.attr("name")] = this.val() || "";
		}

		// send ajax request
		options.url = form.attr("action");
		if (options.data !== undefined) {
			$.each(sendValues, function (i, val) {
				if (options.data[i] === undefined){
					options.data[i] = val;
				}
			});

		} else {
			options.data = sendValues;
		}
		options.type = form.attr("method") || "get";
		return jQuery.ajax(options);
	}
});
