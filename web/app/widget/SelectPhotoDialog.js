_.namespace('App.widget.SelectPhotoDialog');

App.widget.SelectPhotoDialog = Backbone.View.extend({
    el: null,
    modal: null,
    selectedElement: null,
    photoPreviewTmpl: _.template(
            '<p><strong>Selected photo:</strong><br/>{path}</p>' +
            '<p class="media-grid"><a href="{fullUrl}" target="_blank"><img class="thumbnail" src="{thumbUrl}"/></a></p>'
    ),
    directoryPreviewTmpl: _.template('<p><strong>Selected directory:</strong><br/>{path}</p>'),
    initialize: function() {
        var me;

        me = this;

        _.defaults(me.options, {
            showButtonEl: undefined,
            filetreeUrl: undefined,
            photoUrl: undefined,
            onSelect: function() {},
            scope: me
        });

        me.modal = $(me.el).modal({
            backdrop : true,
            keyboard : true,
            show : false
        }).data('modal');

        $(me.options.showButtonEl).click(function() {
            me.modal.show();
        });

        $('button[name=rotateAngle]', me.el).click(function() {
            if (me.selectedElement && me.selectedElement.type == 'file') {
                var rotateAngle = me.getRotateAngle();
                if (me.selectedElement.rotateAngle != rotateAngle) {
                    me.selectedElement.rotateAngle = rotateAngle;
                    me.onChangeSelectedElement();
                }
            }
        })

        me.initFiletree(me.options.filetreeUrl);
    },
    initFiletree: function(filetreeUrl) {
        var me;

        me = this;

        $('.fileTree', me.el).filetree({
            root: '/',
            script: filetreeUrl,
            folderEvent: 'dblclick',
            clickHandler: function (p) {
                me.resetRotateAngle();
                var newSelectedElement, elementChanged;

                if ((p.type == 'file' && p.file.split('.').pop().toLowerCase() in { jpg:1, jpeg:1, png:1 }) || p.type == 'dir') {
                    newSelectedElement = {
                        type: p.type,
                        path: p.file
                    }
                } else {
                    newSelectedElement = null;
                }

                if (newSelectedElement && me.selectedElement && newSelectedElement.path == me.selectedElement.path) {
                    elementChanged = false;
                } else {
                    me.selectedElement = newSelectedElement;
                    me.onChangeSelectedElement();
                }

                if (p.event.type == 'dblclick' && me.selectedElement && me.selectedElement.type == 'file') {
                    me.proccess();
                }
            }
        });
    },
    events: {
        'click button.cancel': 'hide',
        'click button.proccess': 'proccess'
    },
    onChangeSelectedElement: function() {
        var me;

        me = this;

        $('button.proccess', me.el).hide();
        $('.preview', me.el).hide();
        $('.rotateAngle.btngroup').hide();

        if (me.selectedElement) {
            if (me.selectedElement.type == 'file') {
                $('.rotateAngle.btngroup').fadeIn('fast');
            }

            $('.preview', me.el).html(me.getSelectedElementPreviewHtml()).fadeIn('normal');
            $('button.proccess', me.el).show();
        }
    },
    hide: function() {
        this.modal.hide();
    },
    proccess: function() {
        var me, callback;

        me = this;

        if (me.selectedElement) {
            callback = me.options.onSelect;
            scope = me.options.scope;

            callback.call(scope, me.selectedElement);
        }
        me.modal.hide();
    },
    getSelectedElementPreviewHtml: function() {
        var me, html, thumbUrl, fullUrl, path, rotateAngle;

        me = this;
        path = me.selectedElement.path;
        html = '';

        if (me.selectedElement.type == 'file') {
            rotateAngle = me.selectedElement.rotateAngle ? me.selectedElement.rotateAngle : 0;
            thumbUrl = (me.options.photoUrl + '?p=' + path + '&w=100&h=100&r=' + rotateAngle).replace(/"/g, '%22');
            fullUrl = (me.options.photoUrl + '?p=' + path + '&r=' + rotateAngle).replace(/"/g, '%22');

            html = me.photoPreviewTmpl({
                path: path,
                thumbUrl: thumbUrl,
                fullUrl: fullUrl
            });
        } else {
            html = me.directoryPreviewTmpl({
                path: path
            });
        }

        return html;
    },
    resetRotateAngle: function() {
        var me;

        me = this;

        $('button[name=rotateAngle]', me.el).removeClass('active');
        $('button[name=rotateAngle][value=0]', me.el).addClass('active');
    },
    getRotateAngle: function() {
        var me;

        me = this;

        return $('button.active[name=rotateAngle]', me.el).val();
    }
});