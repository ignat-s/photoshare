_.namespace('App.widget.PostProductsAdmin');

App.widget.PostProductsAdmin = Backbone.View.extend({
    el : '#products',
    recordsEl : '#products .records',
    recordTemplate : _.template($('#productTmpl').html()),
    noRecordsFoundMessageEl : '#products .noRecordsFoundMessage',
    productTitleEl: '#products form.add input[name=productTitle]',
    productIdEl: '#products form.add input[name=productId]',
    addFormEl : '#products form.add',
    addButtonEl : '#products button.add',
    initialize: function(options) {
        var me;

        me = this;

        $(me.productTitleEl).autocomplete({
            source: options.productSearchUrl,
            change: function(event, ui) {
                me.onChangeProduct(ui.item ? ui.item.id : null);
            },
            select: function(e, ui) {
                me.onChangeProduct(ui.item ? ui.item.id : null);
            }
        });

        $('.record', me.el).each(function() {
            me.bindProductEvents(this);
        });
    },
    events : {
        'click .btn.add': 'showAddForm',
        'click .btn.cancel': 'hideAddForm',
        'submit form.add': 'onSubmitAddForm'
    },
    onChangeProduct: function(productId) {
        var me;

        me = this;

        $(me.productIdEl).val(productId);
    },
    showAddForm: function() {
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
    bindProductEvents: function(productEl) {
        var me, productId, removeUrl;

        me = this;
        productId = $(productEl).attr('data-record-id');

        $('.btn.remove', productEl).click(function() {
            removeUrl = $(this).attr('href');
            me.showRemoveProduct(productEl, removeUrl);

            return false;
        });
    },
    showRemoveProduct: function(productEl, removeUrl) {
        var me;

        me = this;

        new App.widget.Modal({
            title: 'Remove product from post?',
            callback: function() {
                $(productEl).css('opacity', .5);
                $('.btn.remove', productEl).attr('disabled', 'disabled');
                var success = false;
                $.ajax({
                    scope: me,
                    url: removeUrl,
                    complete: function() {
                        $(productEl).removeClass('removing');
                        $(productEl).css('opacity', 1);
                        $('.btn.remove', productEl).removeAttr('disabled');
                    },
                    success: function(responseData, textStatus, jqXHR) {
                        if (responseData.success) {

                            $(productEl).slideUp('fast', function() {
                                $(productEl).remove();
                                if (!$('.record', me.el).length) {
                                    $(me.noRecordsFoundMessageEl).show();
                                }
                            });
                            success = true;
                        } else {
                            App.alert(responseData.message);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        var responseData = App.parseJSON(jqXHR.responseText);
                        App.alert(responseData.message ? responseData.message : 'Request error: ' + errorThrown);
                    },
                    type: 'POST',
                    dataType: 'json'
                });
            }
        })

        return false;
    },
    onSubmitAddForm: function() {
        var me, formData, url;

        me = this;
        formData = me.getAddFormData();
        url = $(me.addFormEl).attr('action').replace(/\/0$/, '/' + formData.productId);

        if (formData.productId) {
            $('input, submit, reset', me.addFormEl).attr('disabled', 'disabled');
            $.ajax({
                scope: me,
                url: url,
                complete: function() {
                    $('input, submit, reset', me.addFormEl).removeAttr('disabled');
                },
                success: function(responseData, textStatus, jqXHR) {
                    if (responseData.success) {
                        me.addProduct(responseData.data);
                        me.resetAddFormData();
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
        var me, row, productEl, detailsUrl, removeUrl, editUrl;

        me = this;
        row = _.extend(productData, {});

        $(me.noRecordsFoundMessageEl).hide();

        productEl = $(me.recordTemplate(row)).appendTo(me.recordsEl).hide().slideDown('fast');
        me.bindProductEvents(productEl);

        detailsUrl = $('a.btn.details',productEl).attr('href').replace(/\/0\/$/, '/' + productData.id + '/');
        $('a.btn.details',productEl).attr('href', detailsUrl);

        editUrl = $('a.btn.edit', productEl).attr('href').replace(/\/0\/edit\/$/, '/' + productData.id + '/edit/');
        $('a.btn.edit', productEl).attr('href', editUrl);

        removeUrl = $('a.btn.remove', productEl).attr('href').replace(/\/0$/, '/' + productData.id);
        $('a.btn.remove', productEl).attr('href', removeUrl);
    },
    getAddFormData: function() {
        var me;

        me = this;

        return {
            productId : $(me.productIdEl).val()
        }
    },
    resetAddFormData: function() {
        var me;

        me = this;

        $(me.productIdEl).val('')
        $(me.productTitleEl).val('');
    }

});