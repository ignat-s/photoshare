_.namespace('App.widget.tender.PostProductsEdit');

App.widget.PostProductsEdit = Backbone.View.extend({
    el : '#products',
    productRowTemplate : _.template($('#productRowTmpl').html()),
    noRecordsFoundMessageEl : '#products .noRecordsFoundMessage',
    productsTableEl : '#products table',
    productNameEl: '#products form.add input[name=productTitle]',
    productIdEl: '#products form.add input[name=productId]',
    postIdEl: '#products form.add input[name=postId]',
    addFormEl : '#products form.add',
    addButtonEl : '#products button.add',
    addForm: null,
    initialize: function(options) {
        var me;

        me = this;

        $(me.productNameEl).autocomplete({
            source: options.productSearchUrl,
            change: function(event, ui) {
                me.onChangeProduct(ui.item ? ui.item.id : null);
            },
            select: function(e, ui) {
                me.onChangeProduct(ui.item ? ui.item.id : null);
            }
        });
    },
    events : {
        'click .btn.add' : 'showAddForm',
        'click .btn.cancel' : 'hideAddForm',
        'submit form.add' : 'onSubmitAddForm'
    },
    onChangeProduct: function(productId) {
        var me;

        me = this;

        $(me.productIdEl).val(productId);
    },
    showAddForm: function(e) {
        var me;

        me = this;

        $(me.addButtonEl).hide();
        $(me.addFormEl).slideDown();

        return false;
    },
    hideAddForm: function() {
        var me;

        me = this;

        $(me.addFormEl).slideUp('fast', function() {
            $(me.addButtonEl).show();
        });

        return false;
    },
    onSubmitAddForm: function(e) {
        var me, formData, url;

        me = this;
        formData = me.getFormData();
        url = $(me.addFormEl).attr('action');

        if (formData.productId) {
           $('input, submit, reset', me.addFormEl).attr('disabled', 'disabled');
           $.ajax({
                scope: me,
                url: url,
                complete: function() {
                    $('input, submit, reset', me.addFormEl).removeAttr('disabled', 'disabled');
                },
                success: function(responseData, textStatus, jqXHR) {
                    if (responseData.success) {
                        me.addProduct(responseData.data);
                    } else {
                        App.alert(responseData.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var responseData = App.parseJSON(jqXHR.responseText);
                    App.alert(responseData.message ? responseData.message : 'Request error: ' + errorThrown);
                },
                data: formData,
                type: 'POST',
                dataType: 'json'
            });
        }

        return false;
    },
    addProduct: function(productData) {
        var me, row;

        me = this;
        row = _.extend(productData, {
            number: $('tbody tr', me.productsTableEl).length
        });

        if ($(me.productsTableEl).is(":hidden")) {
            $(me.productsTableEl).show();
            $(me.noRecordsFoundMessageEl).hide();
        }

        $('tbody', me.productsTableEl).append(
                me.productRowTemplate(row)
        );

        console.log(productData);
    },
    getFormData: function() {
        var me;

        me = this;
        
        return {
            productId : $(me.productIdEl).val(),
            postId : $(me.postIdEl).val()
        }
    }

});