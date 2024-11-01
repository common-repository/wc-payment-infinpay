<?php
if ( !defined( 'ABSPATH' ) ) exit;
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wito.technology
 * @since             1.0
 * @package           Infinpay
 *
 * @wordpress-plugin
 * Plugin Name:       infinpay
 * Plugin URI:        https://www.infinpay.com
 * Description:       Use infinpay Gateway for your WooCommerce Payment
 * Version:           1.0.0
 * Author:            INFINPAY
 * Author URI:        https://www.infinpay.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-infinpay
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

	function infinpay_wc_not_install()
	{
		echo '<div class="infinpay notice notice-warning is-dismissible">
			<p><strong>infinpay Payment:</strong> WooCommerce not installed, Please install WooCommerce to use this plugin</p>
		</div>';
	}
	
	add_action('admin_notices', 'infinpay_wc_not_install');
	return;
}

// Load Helpers & Libs
require_once plugin_dir_path(__FILE__) . 'helpers.php';
require_once plugin_dir_path(__FILE__) . 'library/infinpay.php';

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('INFINPAY_VERSION', '1.0.0');

function infinpay_activate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-infinpay-activator.php';
	App\Infinpay\Infinpay_Activator::activate();
}


function infinpay_deactivate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-infinpay-deactivator.php';
	App\Infinpay\Infinpay_Deactivator::deactivate();
}


register_activation_hook(__FILE__, 'infinpay_activate');
register_deactivation_hook(__FILE__, 'infinpay_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 */
require plugin_dir_path(__FILE__) . 'includes/class-infinpay.php';

function infinpay_run()
{
	$plugin = new App\Infinpay\Infinpay();
	$plugin->run();
}

infinpay_run();