_.namespace('App.router.PostAdmin');

App.router.PostAdmin = Backbone.Router.extend({
			postProductsAdminWidget : null,
			initialize : function(options) {
				var me = this;

                me.postProductsAdminWidget = new App.widget.PostProductsAdmin({
                    productSearchUrl: options.productSearchUrl
                });

				return me;
			}
		});