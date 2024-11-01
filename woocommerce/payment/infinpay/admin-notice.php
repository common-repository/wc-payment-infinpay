<?php if ( !defined( 'ABSPATH' ) ) exit;?>
<?php foreach ($notices as $n) : ?>
<div class="infinpay notice notice-<?php echo esc_attr($n["type"]) ?> is-dismissible">
    <p><strong><?php echo esc_html($n["title"]); ?>:</strong> <?php echo esc_html($n["message"]); ?></p>
</div>
<?php endforeach; ?>