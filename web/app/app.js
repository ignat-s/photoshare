$(function() {
    /**
     * Create namespace
     */

    if (_.isUndefined(window.App)) {
        window.App = {
            config : {
                env : 'dev',
                routes : {},
                permissions : {}
            }
        };
    }
    App = window.App;

    App.getEnv = function() {
        return App.config.env;
    };

    /**
     * init Server time
     */
    switch (App.config.activeRoute) {
        case 'post_create': case 'post_edit' :
            new App.router.PostAdmin({
                productSearchUrl: App.config.routes.product_search
            });
            break;
    }

    App.alert = function(message) {
        new App.widget.Modal({
            title : 'Attention!',
            html: message,
            primaryText: 'OK',
            secondaryText: 'Cancel'
        });
    }

    App.parseJSON = function(string) {
        var result = {}
        try {
            result = JSON.parse(string);
        } catch (e) {

        }
        return result;
    }
});
