_.namespace('App.router.ProductAdmin');

App.router.ProductAdmin = Backbone.Router.extend({
			productPhotosAdminWidget : null,
			initialize : function(options) {
				var me = this;

                me.productPhotosAdminWidget = new App.widget.ProductPhotosAdmin({
                    
                });

				return me;
			}
		});