// jQuery File Tree Plugin
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// Visit http://abeautifulsite.net/notebook.php?article=58 for more information
//
// Usage: $('.fileTreeDemo').fileTree( options, callback )
//
// Options:  root           - root folder to display; default = /
//           script         - location of the serverside AJAX file to use; default = jqueryFileTree.php
//           folderEvent    - event to trigger expand/collapse; default = click
//           expandSpeed    - default = 500 (ms); use -1 for no animation
//           collapseSpeed  - default = 500 (ms); use -1 for no animation
//           expandEasing   - easing function to use on expand (optional)
//           collapseEasing - easing function to use on collapse (optional)
//           multiFolder    - whether or not to limit the browser to one subfolder at a time
//           loadMessage    - Message to display while initial tree loads (can be HTML)
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// TERMS OF USE
// 
// This plugin is dual-licensed under the GNU General Public License and the MIT License and
// is copyright 2008 A Beautiful Site, LLC. 
//
if (jQuery) (function($) {

    $.extend($.fn, {
        fileTree: function(o, h) {
            // Defaults
            if (!o) var o = {};
            if (o.root == undefined) o.root = '/';
            if (o.script == undefined) o.script = 'jqueryFileTree.php';
            if (o.folderEvent == undefined) o.folderEvent = 'click';
            if (o.expandSpeed == undefined) o.expandSpeed = 100;
            if (o.collapseSpeed == undefined) o.collapseSpeed = 100;
            if (o.expandEasing == undefined) o.expandEasing = null;
            if (o.collapseEasing == undefined) o.collapseEasing = null;
            if (o.multiFolder == undefined) o.multiFolder = true;
            if (o.loadMessage == undefined) o.loadMessage = 'Loading...';
            if (o.clickHandler == undefined) o.clickHandler = function() {
            };
            if (h == undefined) h = function() {
            };

            var selectedItem;

            $(this).each(function() {

                function showTree(c, t) {
                    $(c).addClass('wait');
                    $(".jqueryFileTree.start").remove();
                    $.post(o.script, { dir: t }, function(data) {
                        $(c).find('.start').html('');
                        $(c).removeClass('wait').append(data);
                        if (o.root == t) $(c).find('UL:hidden').show(); else $(c).find('UL:hidden').slideDown({ duration: o.expandSpeed, easing: o.expandEasing });
                        bindTree(c);
                    });
                }

                function bindTree(t) {
                    $(t).find('LI A').bind(o.folderEvent, function() {
                        if ($(this).parent().hasClass('directory')) {
                            if ($(this).parent().hasClass('collapsed')) {
                                // Expand
                                if (!o.multiFolder) {
                                    $(this).parent().parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
                                    $(this).parent().parent().find('LI.directory').removeClass('expanded').addClass('collapsed');
                                }
                                $(this).parent().find('UL').remove(); // cleanup
                                showTree($(this).parent(), escape($(this).attr('rel').match(/.*\//)));
                                $(this).parent().removeClass('collapsed').addClass('expanded');
                            } else {
                                // Collapse
                                $(this).parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
                                $(this).parent().removeClass('expanded').addClass('collapsed');
                            }
                        } else {
                            h($(this).attr('rel'));
                        }
                        return false;
                    });
                    // Prevent A from triggering the # on non-click events
                    //if( o.folderEvent.toLowerCase != 'click' ) $(t).find('LI A').bind('click', function() { return false; });

                    $(t).find('LI A').bind('click dblclick', function(event) {
                        $(selectedItem).removeClass('selected');
                        $(this).addClass('selected');
                        selectedItem = $(this);
                        o.clickHandler({
                            file: $(this).attr('rel'),
                            type: $(this).parent().hasClass('directory') ? 'dir' : 'file',
                            event: event
                        });
                        return false;
                    });
                }


                // Loading message
                $(this).html('<ul class="jqueryFileTree start"><li class="wait">' + o.loadMessage + '<li></ul>');
                // Get the initial file list
                showTree($(this), escape(o.root));
            });
        }
    });

    $.fn.extend({
        insertAtCaret: function(myValue) {
            return this.each(function(i) {
                if (document.selection) {
                    this.focus();
                    sel = document.selection.createRange();
                    sel.text = myValue;
                    this.focus();
                }
                else if (this.selectionStart || this.selectionStart == '0') {
                    var startPos = this.selectionStart;
                    var endPos = this.selectionEnd;
                    var scrollTop = this.scrollTop;
                    this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
                    this.focus();
                    this.selectionStart = startPos + myValue.length;
                    this.selectionEnd = startPos + myValue.length;
                    this.scrollTop = scrollTop;
                } else {
                    this.value += myValue;
                    this.focus();
                }
            })
        }

    });


})(jQuery);

