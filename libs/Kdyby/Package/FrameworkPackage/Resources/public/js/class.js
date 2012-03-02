/**
 * @see http://stackoverflow.com/a/7356528
 *
 * @param functionToCheck
 */
function isFunction(functionToCheck) {
	var getType = {};
	return functionToCheck && getType.toString.call(functionToCheck) == '[object Function]';
}



/**
 * @param num
 * @param dec
 */
function round(num, dec) {
	return Math.round(num * Math.pow(10, dec)) / Math.pow(10, dec);
}


/**
 * @param obj
 * @param method
 */
function callback(obj, method) {
	if (method === undefined) {
		if (isFunction(obj)) {
			return obj;

		} else {
			var arg = obj;
			return function () {
				var obj = arg[0], method = arg[1];
				return obj[method].apply(obj, arguments);
			};
		}

	} else if (obj[method] !== undefined) {
		return function () {
			return obj[method].apply(obj, arguments);
		}
	}
}


/**
 * @author Daniel Steigerwald
 * @author Filip Procházka
 * @see: http://zdrojak.root.cz/clanky/oop-v-javascriptu-iii/
 *
 * @param constructor or methods
 * @param [def]
 */
var $class = function (def) {
	// pokud není konstruktor definován, použijeme nový (nechceme použít zděděný)
	var constructor = def.hasOwnProperty('constructor') ? def.constructor : function() {};

	// proces vytváření třídy rozdělíme do kroků
	for (var name in $class.Initializers) {
		$class.Initializers[name].call(constructor, def[name], def);
	}
	return constructor;
};

/**
 */
$class.Initializers = {
	Extends: function (parent) {
		if (parent) {
			var F = function () { };
			this._superClass = F.prototype = parent.prototype;
			this.prototype = new F;
		}
	},

	Mixins: function (mixins, def) {
		// kostruktoru přidáme metodu mixin
		this.mixin = function (mixin) {
			for (var key in mixin) {
				if (key in $class.Initializers) continue;
				this.prototype[key] = mixin[key];
			}
			this.prototype.constructor = this;
		};
		// a přidanou metodu hned využijeme pro rozšíření prototype
		var objects = [def].concat(mixins || []);
		for (var i = 0, l = objects.length; i < l; i++) {
			this.mixin(objects[i]);
		}
	}
};


/**
 * @param textElement
 */
function selectText(textElement) {
	var doc = document;
	if (doc.body.createTextRange) {
		var range = document.body.createTextRange();
		range.moveToElementText(textElement);
		range.select();
	} else if (window.getSelection) {
		var selection = window.getSelection();
		var range = document.createRange();
		range.selectNodeContents(textElement);
		selection.removeAllRanges();
		selection.addRange(range);
	}
};


/**
 * @see http://stackoverflow.com/q/3517064
 */
$.fn.extend({
	disableSelection:function () {
		this.each(function () {
			if (typeof this.onselectstart != 'undefined') {
				this.onselectstart = function () {
					return false;
				};
			} else if (typeof this.style.MozUserSelect != 'undefined') {
				this.style.MozUserSelect = 'none';
			} else {
				this.onmousedown = function () {
					return false;
				};
			}
		});
	}
});


/**
 * @see http://stackoverflow.com/a/1675345
 */
jQuery.fn.putCursorAtEnd = function () {
	return this.each(function () {
		var me = $(this);
		me.focus();

		if (this.setSelectionRange) {
			// Double the length because Opera is inconsistent about whether a carriage return is one character or two. Sigh.
			var len = me.val().length * 2;
			this.setSelectionRange(len, len);
		} else {
			// (Doesn't work in Google Chrome)
			var val = me.val();
			me.val('');
			me.val(val);
		}

		// Scroll to the bottom, in case we're in a tall textarea
		// (Necessary for Firefox and Google Chrome)
		this.scrollTop = 999999;
	});
};


/**
 * @param arr
 * @return object
 */
var EndlessList = $class({
	constructor: function (arr) {
		var me = this;
		this.first = null;
		this.last = null;
		this.arr = arr;
		this.list = [];

		$.each(arr, function (index, value) {
			if (!me.first) {
				me.first = value;
			}
			me.list.push(value);
			me.last = value;
		});
	},
	get: function(key) {
		return this.arr[key];
	},
	getFirst: function () {
		return this.first;
	},
	getLast: function () {
		return this.last;
	},
	getNextTo: function (item) {
		return this.findNextTo(this.list, item) || this.first;
	},
	getPrevTo: function (item) {
		return this.findNextTo(this.list.slice().reverse(), item) || this.last;
	},
	findNextTo: function (items, searching) {
		var prev, next;
		$.each(items, function (index, value) {
			if (prev === searching) {
				next = value;
				return false;
			}
			prev = value;
		});
		return next;
	}
});
