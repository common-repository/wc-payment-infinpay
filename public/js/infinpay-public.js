(function ($) {
    "use strict";

    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    $(window).load(function () {
        function debounce(func, timeout = 300) {
            var timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    func.apply(this, args);
                }, timeout);
            };
        }

        function hideText() {
            try {
                var label = $(".payment_method_wc_infinpay").find("label")[0];
                var img = $(label).find("img")[0];
                $(label).html(img);
                $(label).show();
            } catch (err) {
                console.warn(err);
            }
        }

        var onSelfChanging = false;
        const hideTextDebounce = debounce(function () {
            if (!onSelfChanging) {
                onSelfChanging = true;
                hideText();
                onSelfChanging = false;
            }
        }, 100);
        hideTextDebounce();

        $(".payment_method_wc_infinpay").on("DOMSubtreeModified", function () {
            hideTextDebounce();
        });
    });
})(jQuery);
