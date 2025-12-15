<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('get_payment_keys')) {
    function get_payment_keys($tableName)
    {
        $paymentSettings = DB::table($tableName)->first();

        if (!$paymentSettings) {
            throw new \Exception('Payment settings not found in database');
        }

        $mode = $paymentSettings->payment_mode == 1 ? 'LIVE' : 'SANDBOX';

        $secretKey = $mode === 'LIVE'
            ? $paymentSettings->live_secret_key
            : $paymentSettings->test_secret_key;

        $publishableKey = $mode === 'LIVE'
            ? $paymentSettings->live_client_id
            : $paymentSettings->test_client_id;

        if (empty($secretKey)) {
            throw new \Exception("Stripe {$mode} secret key is not configured");
        }

        return [
            'mode' => $mode,
            'secretKey' => $secretKey,
            'publishableKey' => $publishableKey,
        ];
    }
}
