<?php
namespace App\Infinpay;
if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://wito.technology
 * @since      1.0.0
 *
 * @package    Infinpay
 * @subpackage Infinpay/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Infinpay
 * @subpackage Infinpay/includes
 * @author     WITO TECGBOLOGY <dev@witotechnology.com>
 */
class Infinpay_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'infinpay',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}