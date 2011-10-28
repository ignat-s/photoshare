_.namespace('App.router.PostEdit');

App.router.PostEdit = Backbone.Router.extend({
			postProductsEditWidget : null,
			initialize : function(options) {
				var me = this;

                me.postProductsEditWidget = new App.widget.PostProductsEdit({
                    productSearchUrl: options.productSearchUrl
                });

				return me;
			}
		});