(function ($) {
    "use strict";

    jQuery(document).ready(function () {
        const selector_env = jQuery("#woocommerce_wc_infinpay_environment");
        function infinpay_settings_hide_show() {
            try {
                var environment = selector_env.val();
                // console.log("environment", environment);
                for (var k of ["merchant_code", "api_key", "api_secret"]) {
                    for (var e of ["live", "sandbox"]) {
                        const key = "woocommerce_wc_infinpay_infinpay_" + e + "_" + k;
                        const hide = `${environment}` !== `${e}`;
                        if (hide) {
                            jQuery("#" + key).closest('tr').hide();
                        } else {
                            jQuery("#" + key).closest('tr').show();
                        }
                    }
                }
            } catch (err) {
                console.error(err);
            }
        }
        selector_env.on("change", infinpay_settings_hide_show);
        infinpay_settings_hide_show();
    });
})(jQuery);
