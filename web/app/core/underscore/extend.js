_.mixin({
	namespace : function(ns) {
		var last, global;

		ns = ns.split('.');
		global = window;
		last = _.last(ns);

		_.each(ns, function(item) {
					if (item != last) {
					} else {
						global[item] = null;
					}
					if (_.isUndefined(global[item])) {
						global[item] = {};
					}
					global = global[item];
				});
	},
	createSequence : function(origFn, newFn, scope) {
		if (!_.isFunction(newFn)) {
			return origFn;
		} else {
			return function() {
				var retval = origFn.apply(this || window, arguments);
				newFn.apply(scope || this || window, arguments);
				return retval;
			};
		}
	},
	isObject : function(obj) {

		var toString = Object.prototype.toString, hasOwnProp = Object.prototype.hasOwnProperty, key;

		if (toString.call(obj) !== "[object Object]")
			return false;

		// own properties are iterated firstly,
		// so to speed up, we can test last one if it is not own

		for (key in obj) {
		};

		return !key || hasOwnProp.call(obj, key);
	},
	isEmpty : function(value, allowEmptyString) {
		return (_.isNull(value))
				|| (_.isNaN(value))
				|| (_.isUndefined(value))
				|| (_.isString(value) && (!allowEmptyString
						? value === ''
						: false)) || (_.isArray(value) && value.length === 0)
				|| (_.isObject(value) && $.isEmptyObject(value))
				|| (!_.isUndefined(value.length) && value.length == 0);
	},
	numberFormat : function(number, format) {
		if (!_.isNumber(number)) {
			return null;
		}

		var hasComma, stripNonNumeric, precisionSplit, numberToString, cnum, parr, j, i, m, n;

		format = format || '0,0.00';
		hasComma = format.indexOf(',') != -1;
		stripNonNumeric = function(str) {
			var rgx, out, i;

			str += '';
			rgx = /^\d|\.|-$/;
			out = '';

			for (i = 0; i < str.length; i++) {
				if (rgx.test(str.charAt(i))) {
					if (!((str.charAt(i) == '.' && out.indexOf('.') != -1) || (str
							.charAt(i) == '-' && out.length != 0))) {
						out += str.charAt(i);
					}
				}
			}
			return out;
		};
		precisionSplit = stripNonNumeric(format).split('.');

		// compute precision
		if (1 < precisionSplit.length) {
			// fix number precision
			number = number.toFixed(precisionSplit[1].length);
		} else if (2 < precisionSplit.length) {
			// error: too many periods
			throw ('NumberFormatException: invalid format, formats should have no more than 1 period: ' + format);
		} else {
			// remove precision
			number = number.toFixed();
		}

		// get the string now that precision is correct
		numberToString = number.toString();

		// format has comma, then compute commas
		if (hasComma) {
			// remove precision for computation
			precisionSplit = numberToString.split('.');

			cnum = precisionSplit[0];

			parr = [];
			j = cnum.length;
			m = Math.floor(j / 3);
			n = cnum.length % 3 || 3; // n cannot be ZERO or causes infinite
			// loop

			// break the number into chunks of 3 digits; first chunk may
			// be less than 3
			for (i = 0; i < j; i += n) {
				if (i != 0) {
					n = 3;
				}
				parr[parr.length] = cnum.substr(i, n);
				m -= 1;
			}

			// put chunks back together, separated by comma
			numberToString = parr.join(',');

			// add the precision back in
			if (precisionSplit[1]) {
				numberToString += '.' + precisionSplit[1];
			}
		}

		// replace the number portion of the format with numberToString
		return format.replace(/[\d,?\.?]+/, numberToString);
	}

});