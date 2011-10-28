_.namespace('Emp.model.Lot');

Emp.model.Lot = Backbone.Model.extend({
	urlRoot : Emp.config.routes.tender_lots,
	defaults : {
		id : null,
		number : null,
		customer : null,
		productName : null,
		unit : null,
		quantity : null,
		biddingStep : null,
		lastPurchasePrice : null,
		lastPurchaseDate : null,
		comment : null
	},
	initialize : function(data, options) {
		if (!_.isEmpty(data.unit) || !_.isEmpty(data.customer)) {
			_.extend(this.attributes, this.associateData(data));
			this.change();
		}
		return this;
	},
	associateData : function(data) {
		_.each(['unit', 'customer'], function(type) {
					if (!_.isEmpty(data[type]) && _.isObject(data[type])) {
						data[type] = new Backbone.Model(data[type]);
					}
				}, this);
		return data;
	},
	getUnit : function() {
		return this.get('unit') ? this.get('unit') : '';
	},
	getCustomer : function() {
		return this.get('customer') ? this.get('customer') : '';
	},
	getBiddingStep : function() {
		return _.numberFormat(this.get('biddingStep'), '0.00');
	},
	getLastPurchasePrice : function() {
		return _.numberFormat(this.get('lastPurchasePrice'), '0.00');
	},
	parse : function(response, xhr) {
		if (response.success) {
			return this.associateData(response.data);
		}
	},
	validate : function(attrs) {
		var errors = {}, fields = ['customer', 'productName', 'unit',
				'quantity', 'biddingStep'];
		_.map(fields, function(name) {
					var value = attrs[name];
					if (_.isEmpty(value)) {
						errors[name] = 'Это поле обязательно для заполнения';
					}
				}, this);
		if (!_.isEmpty(errors)) {
			return errors;
		}
	},
	sync : function(method, model, options) {
		var me, methodMap, getUrl, urlError, type, params, data;

		me = this;
		methodMap = {
			'create' : 'POST',
			'update' : 'PUT',
			'delete' : 'DELETE',
			'read' : 'GET'
		};
		getUrl = function(object) {
			if (!(object && object.url))
				return null;
			return _.isFunction(object.url) ? object.url() : object.url;
		};
		urlError = function() {
			throw new Error('A "url" property or function must be specified');
		};
		type = methodMap[method];
		params = _.extend({
					type : type,
					dataType : 'json'
				}, options);

		// Ensure that we have a URL.
		if (!params.url) {
			params.url = getUrl(model) || urlError();
		}

		// Ensure that we have the appropriate request data.
		if (!params.data && model && (method == 'create' || method == 'update')) {
			params.contentType = 'application/json';
			data = _.clone(me.attributes);

			if (!_.isNaN(parseInt(data.customer, 10))) {
				data.customer = {
					id : data.customer
				};
			}
			if (!_.isNaN(parseInt(data.unit, 10))) {
				data.unit = {
					id : data.unit
				};
			}

			if (method == 'create') {
				delete data.id;
			}
			params.data = JSON.stringify({
						data : data
					});
		}

		// For older servers, emulate JSON by encoding the request into an
		// HTML-form.
		if (Backbone.emulateJSON) {
			params.contentType = 'application/x-www-form-urlencoded';
			params.data = params.data ? {
				data : params.data
			} : {};
		}

		// For older servers, emulate HTTP by mimicking the HTTP method with
		// `_method`
		// And an `X-HTTP-Method-Override` header.
		if (Backbone.emulateHTTP) {
			if (type === 'PUT' || type === 'DELETE') {
				if (Backbone.emulateJSON)
					params.data._method = type;
				params.type = 'POST';
				params.beforeSend = function(xhr) {
					xhr.setRequestHeader('X-HTTP-Method-Override', type);
				};
			}
		}

		// Don't process data on a non-GET request.
		if (params.type !== 'GET' && !Backbone.emulateJSON) {
			params.processData = false;
		}

		// Make the request.
		return $.ajax(params);
	}
});