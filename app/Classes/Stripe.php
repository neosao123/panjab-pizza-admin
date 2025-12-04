<?php

namespace App\Classes;

use Illuminate\Support\Facades\Config;

class Stripe
{
    public function makeStripePayment(float $amount)
    {
        try {

            \Log::info('Stripe Payment Amount', [
                'received_amount' => $amount,
                'amount_type' => gettype($amount),
                'amount_in_cents' => (int)($amount * 100)
            ]);
            // Read mode from ENV
            $mode = env('PAYMENT_MODE', 'SANDBOX');

            // Decide keys based on mode
            if ($mode === 'SANDBOX') {
                $clientSecret = env('TEST_SECRET_KEY');
                $clientId     = env('TEST_CLIENT_ID');
            } else {
                $clientSecret = env('LIVE_SECRET_KEY');
                $clientId     = env('LIVE_CLIENT_ID');
            }

            \Stripe\Stripe::setApiKey($clientSecret);

            // Create customer
            $customer = \Stripe\Customer::create();

            // Create ephemeral key
            $ephemeralKey = \Stripe\EphemeralKey::create(
                ['customer' => $customer->id],
                ['stripe_version' => '2025-11-17.clover']
            );

            $amountInCents = $amount * 100;

            \Log::info('Creating PaymentIntent', [
                'amount_in_cents' => $amountInCents,
                'currency' => 'cad'
            ]);

            // Create payment intent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'cad',
                'customer' => $customer->id,
                'payment_method_types' => ['card', 'link'],
            ]);

            return [
                'paymentIntent'   => $paymentIntent->client_secret,
                'ephemeralKey'    => $ephemeralKey->secret,
                'customer_id'     => $customer->id,
                'publishableKey'  => $clientId,
                'mode'            => $mode,
                'id'              => $paymentIntent->id
            ];
        } catch (\Exception $e) {
            throw new \Exception("Stripe payment error: " . $e->getMessage());
        }
    }
}
