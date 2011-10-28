_.namespace('App.widget.Modal');

App.widget.Modal = Backbone.View.extend({
	el : 'body',
	template : _
			.template('<div id="{id}" class="modal hide fade" style="display: none;">'
					+ '<div class="modal-header">'
					+ '<a href="#" class="close">×</a>'
					+ '<h3>{title}</h3>'
					+ '</div>'
					+ '{html}'
					+ '<div class="modal-footer">'
					+ '<a href="#" class="btn secondary">{secondaryText}</a>'
					+ '<a href="#" class="btn primary">{primaryText}</a>'					
					+ '</div>' + '</div>'),
	templateBody : _.template('<div class="modal-body">' + '{html}' + '</div>'),
	initialize : function() {
		var me, html, bodyHtml, config;

		me = this;
		html = '';
		bodyHtml = '';
		config = me.options;

		_.defaults(config, {
					modalId : 'modal',
					backdrop : true,
					keyboard : true,
					show : true,
					title : '',
					html : '',
					primaryText : 'Yes',
					secondaryText : 'No',
					callback : function() {
					},
					scope : me
				});

		if (!_.isEmpty(config.html)) {
			bodyHtml = me.templateBody({
						html : config.html
					});
		}

		html = me.template({
					id : config.modalId,
					title : config.title,
					html : bodyHtml,
					secondaryText : config.secondaryText,
					primaryText : config.primaryText
				});

		$(me.el).append(html);

		me.getActionEl('primary').click(_.bind(me.onPrimary, me));
		me.getActionEl('secondary').click(_.bind(me.onSecondary, me));

		this.getModalEl().modal({
					backdrop : config.backdrop,
					keyboard : config.keyboard,
					show : config.show
				});

		this.getModalEl().bind('hidden', _.bind(me.destroy, me));
	},
	getModalEl : function() {
		return $('#' + this.options.modalId);
	},
	getActionEl : function(type) {
		return this.getModalEl().find('.modal-footer > .' + type);
	},
	onSecondary : function() {
		this.getModalEl().modal('hide');
		return false;
	},
	onPrimary : function() {
		var callback, scope;

		callback = this.options.callback;
		scope = this.options.scope;

		callback.call(scope);
		this.getModalEl().modal('hide');

		return false;
	},
	destroy : function() {
		this.getActionEl('primary').unbind('click');
		this.getActionEl('secondary').unbind('click');
		this.getModalEl().remove();
	}
});