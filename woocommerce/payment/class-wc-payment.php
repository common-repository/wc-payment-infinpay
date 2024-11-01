<?php
namespace App\Infinpay;
if ( !defined( 'ABSPATH' ) ) exit;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wito.technology
 * @since      1.0.0
 *
 * @package    Infinpay
 * @subpackage Infinpay/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Infinpay
 * @subpackage Infinpay/admin
 * @author     WITO TECGBOLOGY <dev@witotechnology.com>
 */
class InfinPay_App_WC_Payment
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function plugins_loaded()
	{
		require_once dirname(__FILE__) . '/infinpay/class-wc-payment-infinpay-window.php';
		Payment_InfinPay_Window::instance();
	}
}