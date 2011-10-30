_.namespace('App.widget.ProductPhotosAdmin');

App.widget.ProductPhotosAdmin = Backbone.View.extend({
    el : '#photos',
    recordsEl: '#photos .records',
    recordTemplate : _.template($('#photoTmpl').html()),
    removeSelectedPhotosEl: '#photos button.removeSelectedPhotos',
    noRecordsFoundMessageEl : '#photos .noRecordsFoundMessage',
    selectPhotoDialog: null,
    showSelectPhotoDialogButtonEl: '#photos .btn.showAddDialog',
    initialize: function() {
        var me;

        me = this;

        me.selectPhotoDialog = new App.widget.SelectPhotoDialog({
            el: '#addPhotoDialog',
            showButtonEl: me.showSelectPhotoDialogButtonEl,
            filetreeUrl: App.config.routes.photostorage_filetree,
            photoUrl: App.config.routes.photostorage_photo,
            onSelect: me.proccessAdd,
            scope: me
        });
    },
    events : {
        'click button.remove': 'removeSelectedPhotos'
    },
    proccessAdd: function(data) {
        var me;

        me = this;
        $(me.showSelectPhotoDialogButtonEl).attr('disabled', 'disabled');

        $.ajax({
            scope: me,
            url: App.config.routes.product_photo_add,
            complete: function() {
                $(me.showSelectPhotoDialogButtonEl).removeAttr('disabled');
            },
            success: function(responseData, textStatus, jqXHR) {
                if (responseData.success) {
                    me.addPhoto(responseData.data);
                } else {
                    App.alert(responseData.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var responseData = App.parseJSON(jqXHR.responseText);
                App.alert(responseData.message ? responseData.message : 'Request error: ' + errorThrown);
            },
            data: data,
            type: 'POST',
            dataType: 'json'
        });
    },
    addPhoto: function(data) {
        var me, html, photoEl;

        me = this;

        data.path = data.path.replace(/"/g, '%22');

        html = me.recordTemplate(data);
        if (data.id) {
            html = html.replace(/id=0/g, 'id=' + data.id);
        } else {
            html = html.replace(/id=0/g, 'p=' + data.path);
            html = html.replace(/data-record-id=""/g, 'data-record-id="' + data.hash + '"');
            html = html.replace(/name="delete\[\]"/g, 'name="delete[' + data.hash + ']"');
            html = html.replace(/id="delete_"/g, 'id="delete_' + data.hash +  '"');
            html = html.replace(/for="delete_"/g, 'for="delete_' + data.hash +  '"');
        }
        
        html = html.replace(/r=0/g, 'r=' + me.encodeRotateAngle(data.rotateAngle));

        photoEl = $(html).appendTo(me.recordsEl);
        $(me.noRecordsFoundMessageEl).hide();
        App.applyColorbox(photoEl);
    },
    removeSelectedPhotos: function() {
        var me, selectedIds;

        me = this;
        selectedIds = [];
        $('#photos .records input:checked').each(function() {
            var id = this.name.match(/delete\[([^\]]+)\]/)[1];
            selectedIds.push(id);
        })

        if (selectedIds.length) {
            new App.widget.Modal({
                title: 'Remove selected photos from product?',
                callback: function() {
                    $(me.removeSelectedPhotosEl).attr('disabled', 'disabled');
                    $.ajax({
                        scope: me,
                        url: App.config.routes.product_photo_remove,
                        complete: function() {
                            $(me.removeSelectedPhotosEl).removeAttr('disabled');
                        },
                        success: function(responseData, textStatus, jqXHR) {
                            if (responseData.success) {
                                me.removePhotos(selectedIds);
                            } else {
                                App.alert(responseData.message);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            var responseData = App.parseJSON(jqXHR.responseText);
                            App.alert(responseData.message ? responseData.message : 'Request error: ' + errorThrown);
                        },
                        data: {
                            'photos': selectedIds
                        },
                        type: 'POST',
                        dataType: 'json'
                    });
                }
            });
        }
    },
    removePhotos: function(ids) {
        var me, selectedIds;

        me = this;

        $.each(ids, function() {
            $('.records .record[data-record-id=' + this + ']', me.el).remove();
        })

        if (!$('.records .record', me.el).length) {
            $(me.noRecordsFoundMessageEl).show();
        }
    },
    encodeRotateAngle: function(rotateAngle)
    {
        return rotateAngle == -90 ? '90cw' : (rotateAngle == 90 ? '90ccw' : (rotateAngle == 180 ? 180 : 0));
    }
});