<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('infinpay_data_get')) {
    function infinpay_data_get($data, $key, $default = null)
    {
        if (isset($data[$key])) {
            return $data[$key];
        }
        return $default;
    }
}

if (!function_exists('infinpay_get_client_ip')) {
    function infinpay_get_client_ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        } else {
            return sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }
    }
}

if (!function_exists('infinpay_data_esc_sanitize_get')) {
    function infinpay_data_esc_sanitize_get($data, $key, $default = null)
    {
        return sanitize_text_field(esc_attr(infinpay_data_get($data, $key, $default)));
    }
}
