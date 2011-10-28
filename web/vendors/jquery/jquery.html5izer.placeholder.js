(function ($) {
    $.fn.placeholder = function (options) {

        var defaults = { placeholderTextColour: '#CCC' }; //set defaults

        var opts = $.extend(defaults, options); //merge any options passed with the defaults

        return this.each(function () { //loop through every object this is to apply to

            var attr = $(this).attr('placeholder');

            if (typeof attr !== 'undefined' && attr !== false && this.nodeName.toLowerCase() == 'input') {

                var thisOpts = $.metadata ? $.extend({}, opts, $(this).metadata()) : opts; //merge any metadata options with the current settings

                if ($(this).val() == '') {
                    $(this).val(attr).css('color', thisOpts.placeholderTextColour);
                }
                $(this).focus(function () {
                    if ($(this).val() == attr) {
                        $(this).val('').css('color', '');
                    }
                });

                $(this).blur(function () {
                    if ($(this).val() == '') {
                        $(this).val(attr).css('color', thisOpts.placeholderTextColour);
                    }
                });
            }
        });
    };

})(jQuery);