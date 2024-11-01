<?php

namespace App\Infinpay;
if ( !defined( 'ABSPATH' ) ) exit;
class InfinPay_Lib
{
    public const STATUS_NO_ERROR = "0";
    public const STATUS_MERCHANT_CODE_KEY_ID_MISMATCH = "2500";
    public const STATUS_DUPLICATED_MERCHANT_REFERENCE = "2501";

    public static function statusMessage($status)
    {
        switch ($status) {
            case self::STATUS_DUPLICATED_MERCHANT_REFERENCE:
                return "Merchant code and key id mismatch.";
            case self::STATUS_DUPLICATED_MERCHANT_REFERENCE:
                return "Transaction with merchant reference: <merchant_reference> already exist. Please use another value.";
            default:
                return "status [$status]";
        }
    }

    // ---
    private $merchantCode;
    private $apiKey;
    private $apiSecret;
    private $currency;

    private $host;

    function __construct($config)
    {
        $this->merchantCode = $config["merchant_code"];
        $this->apiKey = $config["api_key"];
        $this->apiSecret = $config["api_secret"];
        $this->host = $config["host"];

        $this->currency = (isset($config["currency"]) && $config["currency"]) ? $config["currency"] : "MYR";
    }

    public function compose_payment_window($request)
    {
        $endpoint = $this->host . "/api/pymt/pw/v1/payment";
        $fields = [
            "merchant_code" => $this->merchantCode,
            "merchant_reference" => infinpay_data_get($request, "merchant_reference"),
            "currency" => infinpay_data_get($request, "currency", $this->currency),
            "amount" => infinpay_data_get($request, "amount"),
            "description" => infinpay_data_get($request, "description"),
            "response_url" => infinpay_data_get($request, "response_url"),

            "customer" => [
                "customer_id" => infinpay_data_get($request, "customer_id"),
                "customer_name" => infinpay_data_get($request, "customer_name"),
                "customer_contact_no" => trim(infinpay_data_get($request, "customer_contact_no"), "+"),
                "customer_email" => infinpay_data_get($request, "customer_email"),
            ],
            "billing" => [
                "billing_address_line_1" => infinpay_data_get($request, "billing_address_line_1"),
                "billing_address_line_2" => infinpay_data_get($request, "billing_address_line_2"),
                "billing_address_line_3" => infinpay_data_get($request, "billing_address_line_3"),
                "billing_address_city" => infinpay_data_get($request, "billing_address_city"),
                "billing_address_state" => infinpay_data_get($request, "billing_address_state"),
                "billing_address_postal_code" => infinpay_data_get($request, "billing_address_postal_code"),
                "billing_address_country_code" => infinpay_data_get($request, "billing_address_country_code"),
            ],
            "shipping" => [
                "shipping_address_line_1" => infinpay_data_get($request, "shipping_address_line_1"),
                "shipping_address_line_2" => infinpay_data_get($request, "shipping_address_line_2"),
                "shipping_address_line_3" => infinpay_data_get($request, "shipping_address_line_3"),
                "shipping_address_city" => infinpay_data_get($request, "shipping_address_city"),
                "shipping_address_state" => infinpay_data_get($request, "shipping_address_state"),
                "shipping_address_postal_code" => infinpay_data_get($request, "shipping_address_postal_code"),
                "shipping_address_country_code" => infinpay_data_get($request, "shipping_address_country_code"),
            ]
        ];
        $sign = $this->sign($fields);
        return  [
            "action" => $endpoint,
            "method" => "POST",
            "jwt" => $sign["jwt"],
            "fields" => $fields,
        ];
    }

    public function sign($data)
    {
        $header = self::base64_url_encode(json_encode([
            "kid" => $this->apiKey,
            "alg" => "HS256"
        ]));
        $payload = is_string($data) ? self::base64_url_encode($data)  : self::base64_url_encode(json_encode($data));
        $signature = self::base64_url_encode(hash_hmac("sha256", $header . "." . $payload, base64_decode($this->apiSecret), true));

        return [
            "header" =>  $header,
            "payload" => $payload,
            "signature" => $signature,
            "jwt" => $header . "." . $payload . "." . $signature
        ];
    }

    public function decode($response)
    {
        $parts = explode(".", $response);
        $header = base64_decode($parts[0]);
        $payload = base64_decode($parts[1]);

        $d_signature = $parts[2];
        $signature = $this->sign($payload)["signature"];

        return [
            "header" => $header,
            "payload" => json_decode($payload, true),
            "signature" => $signature,
            "valid" => $d_signature === $signature
        ];
    }

    // --
    private static function base64_url_encode($text)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }
}