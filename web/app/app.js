$(function() {
    $('.toggle.btn').toggleBtn();
    $('.radio .btn').radio();
    $("input.datepicker").datepicker({
        'dateFormat' : 'yy/mm/dd'
    });

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
        case 'post_create':
        case 'post_edit' :
            new App.router.PostAdmin({
                productSearchUrl: App.config.routes.product_search
            });
            break;
        case 'product_create':
        case 'product_edit' :
            new App.router.ProductAdmin({
                //productSearchUrl: App.config.routes.product_search
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

    App.applyColorbox = function(el) {
        $("a.colorbox", el).colorbox({
            maxWidth: '90%',
            maxHeight: '90%'
        });
    }

});
