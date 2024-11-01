<?php

namespace App\Infinpay;

use WC_Payment_Gateway;

defined('ABSPATH') || exit;

class Payment_InfinPay_Window extends WC_Payment_Gateway
{
	private static $_instance;
	public static function instance()
	{
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private $is_debug = false;

	private $flag = [];
	private $instructions;

	private $environment = "live";

	private $infinpay_host;
	private $infinpay_merchant_code;
	private $infinpay_api_key;
	private $infinpay_api_secret;

	function __construct()
	{
		$this->id = "wc_infinpay";
		$this->icon = plugin_dir_url(__FILE__) . 'assets/img/payment-logo.png';
		$this->has_fields = false;
		$this->method_title = "infinpay";
		$this->method_description = "infinpay is a payment service provider that offer businesses to seamlessly accept payments in the remote environment, we offer a range of payment channels such as Credit/Debit cards, Online Banking, and e-Wallets.";

		$this->init_form_fields();
		$this->init_settings();

		$this->init();
	}

	/**
	 * @return InfinPay_Lib
	 */
	private function get_infinpay()
	{
		return new InfinPay_Lib([
			"host" => $this->infinpay_host,
			"merchant_code" => $this->infinpay_merchant_code,
			"api_key" => $this->infinpay_api_key,
			"api_secret" => $this->infinpay_api_secret
		]);
	}

	private function init()
	{
		// Define user set variables
		$this->title        = $this->get_option('title', 'infinpay');
		$this->description  = $this->get_option('description', 'Pay securely using your credit card or online banking through infinpay.');

		$this->is_debug = $this->get_option('is_debug', false);

		$this->environment = $this->get_option('environment', 'live');
		if ($this->environment === 'live') {
			$this->infinpay_host = "https://paymenthub.infinpay.com";
			$this->infinpay_merchant_code    = $this->get_option('infinpay_live_merchant_code');
			$this->infinpay_api_key    = $this->get_option('infinpay_live_api_key');
			$this->infinpay_api_secret   = $this->get_option('infinpay_live_api_secret');
		} else {
			// TODO: get the sandbox host
			$this->infinpay_host = "https://uatpaymenthub.infinitium.com";
			$this->infinpay_merchant_code    = $this->get_option('infinpay_sandbox_merchant_code');
			$this->infinpay_api_key    = $this->get_option('infinpay_sandbox_api_key');
			$this->infinpay_api_secret   = $this->get_option('infinpay_sandbox_api_secret');
		}

		// --- wc hooks
		add_filter('woocommerce_payment_gateways', array($this, 'register'));

		// actions
		add_action('woocommerce_update_options_payment_gateways_' . 'wc_infinpay', array($this, 'process_admin_options'));
		add_action('woocommerce_thankyou_' . 'wc_infinpay', array($this, 'thankyou_page'));

		// Customer Emails
		// add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

		add_filter('template_redirect', array($this, 'infinpay_payment_redirect'));
		add_filter('template_include', array($this, 'infinpay_payment_reponse'));

		add_action('admin_notices', array($this, 'notice'));
	}

	public function register($gateways)
	{
		$gateways[] = __CLASS__;
		return $gateways;
	}

	public function notice()
	{
		$notices = [];
		$incomplete = empty($this->infinpay_merchant_code) || empty($this->infinpay_api_key) || empty($this->infinpay_api_secret);
		if ($incomplete) {
			array_push($notices, [
				"type" => "warning",
				"title" => __("infinpay", 'wc_infinpay'),
				"message" => __("Incomplete settings, please fill in the infinpay merchat code, api key and secret.", 'wc_infinpay')
			]);
		}

		if (count($notices) > 0) {
			include_once(dirname(__FILE__) . '/admin-notice.php');
		}
	}

	public function init_form_fields()
	{
		$this->form_fields = apply_filters('wc_offline_form_fields', array(
			'is_debug' => array(
				'title'   => __('Debug', 'wc_infinpay'),
				'type'    => 'checkbox',
				'label'   => __('Enable/Disable Debug Mode', 'wc_infinpay'),
				'description'   => __('When debug mode is active, all payment errors are collected and stored in WooCommerce > Status > Logs. ', 'wc_infinpay'),
				'default' => 'no'
			),

			'enabled' => array(
				'title'   => __('Enable/Disable', 'wc_infinpay'),
				'type'    => 'checkbox',
				'label'   => __('Enable infinpay Payment', 'wc_infinpay'),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __('Title', 'wc_infinpay'),
				'type'        => 'text',
				'description' => __('This controls the title for the payment method the customer sees during checkout.', 'wc_infinpay'),
				'default'     => __('infinpay', 'wc_infinpay'),
				'desc_tip'    => true,
			),

			'description' => array(
				'title'       => __('Description', 'wc_infinpay'),
				'type'        => 'textarea',
				'description' => __('Payment method description that the customer will see on your checkout.', 'wc_infinpay'),
				'default'     => __('Pay securely using your credit card or online banking through infinpay', 'wc_infinpay'),
				'desc_tip'    => true,
			),

			'environment' => array(
				'title'   => __('Environment', 'wc_infinpay'),
				'type'    => 'select',
				'label'   => __('Select the Environment', 'wc_infinpay'),
				'default' => 'live',
				'options' => array(
					'live' => 'Live',
					'sandbox' => 'Sandbox'
				)
			),

			'infinpay_live_merchant_code' => array(
				'title'       => __('Live Merchant Code', 'wc_infinpay'),
				'type'        => 'text',
				'description' => __('This is the Merchant Code that you can obtain from Merchant Profile in your Merchant Portal.​', 'wc_infinpay'),
				'default'     => '',
				'desc_tip'    => true,
			),
			'infinpay_live_api_key' => array(
				'title'       => __('Live Key ID​', 'wc_infinpay'),
				'type'        => 'text',
				'description' => __('This is the Key ID that you can obtain from Merchant Profile in your Merchant Portal.​', 'wc_infinpay'),
				'default'     => '',
				'desc_tip'    => true,
			),
			'infinpay_live_api_secret' => array(
				'title'       => __('Live Secret Key​', 'wc_infinpay'),
				'type'        => 'password',
				'description' => __('This is the Secret Key that you can obtain from Merchant Profile in your Merchant Portal.​', 'wc_infinpay'),
				'default'     => '',
				'desc_tip'    => true,
			),


			'infinpay_sandbox_merchant_code' => array(
				'title'       => __('Sandbox Merchant Code', 'wc_infinpay'),
				'type'        => 'text',
				'description' => __('This is the sandbox Merchant Code that you can obtain from infinpay.', 'wc_infinpay'),
				'default'     => '',
				'desc_tip'    => true,
			),
			'infinpay_sandbox_api_key' => array(
				'title'       => __('Sandbox Key ID', 'wc_infinpay'),
				'type'        => 'text',
				'description' => __('This is the sandbox Key ID that you can obtain from infinpay.', 'wc_infinpay'),
				'default'     => '',
				'desc_tip'    => true,
			),
			'infinpay_sandbox_api_secret' => array(
				'title'       => __('Sandbox Secret Key', 'wc_infinpay'),
				'type'        => 'password',
				'description' => __('This is the sandbox Secret Key that you can obtain from infinpay.', 'wc_infinpay'),
				'default'     => '',
				'desc_tip'    => true,
			),
		));
	}

	public function process_payment($order_id)
	{
		global $woocommerce;
		$order = wc_get_order($order_id);

		// Mark as on-hold (we're awaiting the payment)
		$order->update_status('on-hold', __('Awaiting infinpay payment', 'wc_infinpay'));

		// TODO: how to redirect with the form values?

		// clear cart (order already put to on-hold)
		WC()->cart->empty_cart();

		return array(
			'result' => 'success',
			'redirect' => sanitize_url($this->get_return_url($order) . "&infinpay-window-redirect=1")
		);
	}

	public function thankyou_page()
	{
		if ($this->instructions) {
			echo wpautop(wptexturize(esc_html($this->instructions)));
		}
	}

	// ----
	public function infinpay_payment_redirect()
	{
		if (infinpay_data_esc_sanitize_get($_REQUEST, "infinpay-window-redirect")) {

			$order = wc_get_order(infinpay_data_esc_sanitize_get($_REQUEST, "order-received"));

			$order->get_id();

			// generate transaction id (merchant reference)
			// format: wc<order id>-<random id>
			$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
			$transactionId = strtolower("wc" . $order->get_id() . "-" . uniqid()  . substr(str_shuffle($permitted_chars), 0, 10));
			$order->set_transaction_id(sanitize_text_field($transactionId));

			$order->save();

			$compose = $this->get_infinpay()->compose_payment_window([
				"merchant_reference" => sanitize_text_field($transactionId),
				"amount" => sanitize_text_field($order->get_total()),
				"description" => sanitize_text_field("Order " . $order->get_id()),
				"response_url" => sanitize_url($this->get_return_url($order) . "&infinpay-window-response=1"),

				"customer_id" => sanitize_text_field($order->get_customer_id()),
				"customer_name" => sanitize_text_field($order->get_billing_first_name()) . " " . sanitize_text_field($order->get_billing_last_name()),
				"customer_contact_no" => wc_sanitize_phone_number($order->get_billing_phone()),
				"customer_email" => sanitize_email($order->get_billing_email()),

				"billing_address_line_1" => sanitize_text_field($order->get_billing_address_1()),
				"billing_address_line_2" => sanitize_text_field($order->get_billing_address_2()),
				"billing_address_line_3" => "",
				"billing_address_city" => sanitize_text_field($order->get_billing_city()),
				"billing_address_state" => sanitize_text_field($order->get_billing_state()),
				"billing_address_postal_code" => sanitize_text_field($order->get_billing_postcode()),
				"billing_address_country_code" => sanitize_text_field($order->get_billing_country()),

				"shipping_address_line_1" => sanitize_text_field($order->get_shipping_address_1()),
				"shipping_address_line_2" => sanitize_text_field($order->get_shipping_address_2()),
				"shipping_address_line_3" => "",
				"shipping_address_city" => sanitize_text_field($order->get_shipping_city()),
				"shipping_address_state" => sanitize_text_field($order->get_shipping_state()),
				"shipping_address_postal_code" => sanitize_text_field($order->get_shipping_postcode()),
				"shipping_address_country_code" => sanitize_text_field($order->get_shipping_country()),
			]);
			include_once(dirname(__FILE__) . '/redirect-payment.php');

			if ($this->is_debug) {
				wc_get_logger()->debug(wc_print_r([
					"order_id" => $order->get_id(),
					"infinpay_form_action" => infinpay_data_get($compose, "action"),
					"infinpay_form_method" => infinpay_data_get($compose, "method"),
					"infinpay_form_jwt" => infinpay_data_get($compose, "jwt")
				], true), ["source" => "infinpay-payment-redirect"]);
			}
			die();
		}
	}

	public function infinpay_payment_reponse($original_template)
	{
		$infinpay_window_response = infinpay_data_esc_sanitize_get($_REQUEST, "infinpay-window-response");
		$infinpay_window_response_backend = infinpay_data_esc_sanitize_get($_REQUEST, "infinpay-window-response-backend");

		if ($infinpay_window_response || $infinpay_window_response_backend) {
			$is_backend = $infinpay_window_response_backend;

			$jwt = infinpay_data_esc_sanitize_get($_REQUEST, "jwt");
			$decoded = $this->get_infinpay()->decode($jwt);

			if ($decoded["valid"]) {
				if ($this->is_debug) {
					wc_get_logger()->debug(wc_print_r([
						"client_ip" => infinpay_get_client_ip(),
						"decoded" => esc_html($decoded)
					], true), ["source" => "infinpay-payment-response"]);
				}
				$this->wc_record_payment_response($decoded["payload"], $is_backend);
			} else {
				$d_order_id = infinpay_data_get($decoded, "order-received");
				wc_get_logger()->error("invalid signature payment response on order $$d_order_id", ["source" => "infinpay-payment-response"]);
			}
		}

		return $original_template;
	}

	// ----
	private function wc_record_payment_response($data, $is_backend)
	{
		$d_transaction_id = sanitize_text_field(infinpay_data_get($data, "merchant_reference"));
		$orders = wc_get_orders(array(
			'limit' => 1,
			'transaction_id' => sanitize_text_field($d_transaction_id),
		));

		$order = empty($orders) ? null : $orders[0];
		if ($order) {

			if ($this->is_debug) {
				wc_get_logger()->debug(wc_print_r(esc_html($data), true), ["source" => "infinpay-payment-process"]);
			}

			/* 
			"transaction": { 
				"result_code": 0,
				"result_description": "No Error",
				"transaction_reference": "26162ce1a8f74465af5f518a260fd21d", 
				"transaction_timestamp": "1645171887670",
				"currency": "MYR",
				"amount": "552.50",
				"payment_status": " AUTHORIZED",
				"acquirer_profile": " 655cd1eb0f0940f3910b421f0f1275c3 ",
				"acquirer_reference": "bb7ab7b900984c5c94cee9ddce6a8ef4", 
				"acquirer_auth_id": "8ace1b",
				"acquirer_response_code": "AA",
				"acquirer_response_message": "Authorized"
				}, 
				"card": {

				}, 
				"instalment": {
					"instalment_plan_code": "012", "instalment_plan_tenure": 12”
				},
				"merchant_code": "26aa88fe65c546e88255f21169b6cf6d", 
				"merchant_reference": "jnrbGYUdiWPBbnb1sRvzlQLzxOUCYsKy" */

			// get data 
			$d_transaction = infinpay_data_get($data, "transaction");
			$d_transaction_reference = infinpay_data_get($d_transaction, "transaction_reference");

			$d_result_code = infinpay_data_get($d_transaction, "result_code");
			$d_result_description = infinpay_data_get($d_transaction, "result_description");


			// refer WooCommerce Managing Order: https://woocommerce.com/document/managing-orders/

			// wc: set order status
			$success = "$d_result_code" === InfinPay_Lib::STATUS_NO_ERROR;
			if ($order->get_status() === "on-hold") {
				if ($success) {
					// mark payment done
					if (!$order->is_paid()) {
						$order->payment_complete();

						// TODO: get payment method from response payload (wait customer advise)
						//$order->set_payment_method_title("TEST INFINPAY"); 
					}

					$order->update_status('completed', __('Completed', 'wc_infinpay'));
					// wc: reduce stock
					wc_reduce_stock_levels($order->get_id()); // should reduce b4 payment ?
					$order->add_order_note("success payment on transaction (" . $d_transaction_reference . ")");
					wc_get_logger()->notice(
						"success payment transaction reference " . $d_transaction_reference,
						["source" => "infinpay-payment-order"]
					);
				} else {
					$order->update_status('failed', __('Failed', 'wc_infinpay'));
					$order->add_order_note("failed payment on transaction (" . $d_transaction_reference . "), reason: " . $d_result_description);
					wc_get_logger()->notice(
						"failed payment transaction reference " . $d_transaction_reference . ": " . $d_result_description,
						["source" => "infinpay-payment-order"]
					);
				}
			}
		}
	}
}
