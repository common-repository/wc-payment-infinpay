<?php if ( !defined( 'ABSPATH' ) ) exit; ?>
<html>

<head>
    <title>Redirect to Payment Gateway</title>
</head>

<body>
    <div>
        <span>Redirecting to infinpay payment gateway, please do not close or refresh this window</span><br />
        <form id="infinpay-payment-form" method="<?php echo esc_attr($compose["method"]) ?>"
            action="<?php echo esc_attr($compose["action"]) ?>">
            <input type="hidden" name="jwt" value="<?php echo esc_attr($compose["jwt"]) ?>" />
        </form>
    </div>
</body>
<script>
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        document.getElementById("infinpay-payment-form").submit();
    }, 1000);
});
</script>

</html>