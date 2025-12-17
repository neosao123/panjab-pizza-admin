<?php

namespace App\Classes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Stripe
{
    private $paymentSettings;
    private $mode;
    private $secretKey;
    private $clientId;

    public function __construct()
    {
        // Get payment settings from database
        $this->paymentSettings = DB::table('payment_settings')->first();

        if (!$this->paymentSettings) {
            throw new \Exception('Payment settings not found in database');
        }

        // Determine mode: 0 = sandbox, 1 = live
        $this->mode = $this->paymentSettings->payment_mode == 1 ? 'LIVE' : 'SANDBOX';

        // Set keys based on mode
        if ($this->mode === 'LIVE') {
            $this->secretKey = $this->paymentSettings->live_secret_key;
            $this->clientId = $this->paymentSettings->live_client_id;
        } else {
            $this->secretKey = $this->paymentSettings->test_secret_key;
            $this->clientId = $this->paymentSettings->test_client_id;
        }

        if (empty($this->secretKey) || empty($this->clientId)) {
            throw new \Exception("Stripe {$this->mode} credentials are not configured");
        }
    }

    public function makeStripePayment(float $amount)
    {
        try {
            Log::info('Stripe Payment Amount', [
                'received_amount' => $amount,
                'amount_type' => gettype($amount),
                'amount_in_cents' => (int)($amount * 100),
                'mode' => $this->mode
            ]);

            \Stripe\Stripe::setApiKey($this->secretKey);

            // Create customer
            $customer = \Stripe\Customer::create();

            // Create ephemeral key
            $ephemeralKey = \Stripe\EphemeralKey::create(
                ['customer' => $customer->id],
                ['stripe_version' => '2025-11-17.clover']
            );

            $amountInCents = (int)($amount * 100);

            Log::info('Creating PaymentIntent', [
                'amount_in_cents' => $amountInCents,
                'currency' => 'cad',
                'mode' => $this->mode
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
                'publishableKey'  => $this->clientId,
                'mode'            => $this->mode,
                'id'              => $paymentIntent->id
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment error', [
                'error' => $e->getMessage(),
                'mode' => $this->mode
            ]);
            throw new \Exception("Stripe payment error: " . $e->getMessage());
        }
    }

    /**
     * Create Payment Link
     */
    public function createPaymentLink(float $amount, string $orderId, string $description = 'Payment')
    {
        try {
            \Stripe\Stripe::setApiKey($this->secretKey);

            $amountInCents = (int)($amount * 100);

            Log::info('Creating Stripe Payment Link', [
                'amount' => $amount,
                'amount_in_cents' => $amountInCents,
                'order_id' => $orderId,
                'mode' => $this->mode
            ]);

            // Create a product
            $product = \Stripe\Product::create([
                'name' => $description,
                'metadata' => ['order_id' => $orderId]
            ]);

            // Create a price for the product
            $price = \Stripe\Price::create([
                'product' => $product->id,
                'unit_amount' => $amountInCents,
                'currency' => 'cad',
            ]);

            // Create the payment link
            $paymentLink = \Stripe\PaymentLink::create([
                'line_items' => [
                    [
                        'price' => $price->id,
                        'quantity' => 1,
                    ],
                ],
                'metadata' => [
                    'order_id' => $orderId,
                ],
                'payment_intent_data' => [
                    'metadata' => [
                        'order_id' => $orderId
                    ]
                ],
                'restrictions' => [
                    'completed_sessions' => [
                        'limit' => 1,  // Only 1 successful payment allowed
                    ],
                ]
                //,
                /*'after_completion' => [
                    'type' => 'redirect',
                    'redirect' => [
                        'url' => env('FRONTEND_URL') . 'payment/success?order_id=' . $orderId,
                    ],
                ],*/
            ]);

            Log::info('Stripe Payment Link Created', [
                'payment_link_id' => $paymentLink->id,
                'url' => $paymentLink->url,
                'mode' => $this->mode
            ]);

            return [
                'success' => true,
                'payment_link_id' => $paymentLink->id,
                'payment_url' => $paymentLink->url,
                'product_id' => $product->id,
                'price_id' => $price->id,
                'mode' => $this->mode,
                'publishableKey' => $this->clientId,
                'amount' => $amount,
                'currency' => 'CAD'
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment link creation error', [
                'error' => $e->getMessage(),
                'mode' => $this->mode
            ]);
            throw new \Exception("Failed to create payment link: " . $e->getMessage());
        }
    }




}
