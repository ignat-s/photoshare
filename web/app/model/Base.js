_.namespace('Emp.model.Base');
Emp.model.Base = Backbone.Model.extend({
	sync : function(method, model, options) {
		var me, methodMap, getUrl, urlError, type, params;

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

			params.data = JSON.stringify({
						data : me._getDataParam(method)
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
	},
	_getDataParam : function(method) {
		var data = _.clone(this.attributes);
		if (method == 'create') {
			delete data.id;
		}
		return data;
	}
});