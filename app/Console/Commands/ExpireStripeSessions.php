<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class ExpireStripeSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:expire-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire Stripe checkout sessions and deactivate payment links for orders with expired payment time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Stripe session/payment link expiration process...');

        try {
            // Get payment settings to determine which API key to use
            $paymentSettings = DB::table('payment_settings')->first();

            if (!$paymentSettings) {
                $this->error('Payment settings not found in database');
                return Command::FAILURE;
            }

            // Determine which secret key to use based on mode
            $secretKey = $paymentSettings->payment_mode == 1
                ? $paymentSettings->live_secret_key
                : $paymentSettings->test_secret_key;

            if (empty($secretKey)) {
                $this->error('Stripe secret key not configured');
                return Command::FAILURE;
            }

            // Initialize Stripe client
            $stripe = new StripeClient($secretKey);
            $mode = $paymentSettings->payment_mode == 1 ? 'LIVE' : 'TEST';
            $this->info("Using {$mode} mode");

            // Count total expired orders (excluding COD)
            $totalExpired = OrderMaster::where('payment_expires_at', '<', now())
                ->where('paymentStatus', 'pending')
                ->where(function ($query) {
                    $query->whereNotNull('stripesessionid')
                        ->orWhereNotNull('payment_link_id');
                })
                ->count();

            if ($totalExpired === 0) {
                $this->info('No expired sessions/payment links found.');
                return Command::SUCCESS;
            }

            $this->info("Found {$totalExpired} expired order(s) to process.");

            $successCount = 0;
            $errorCount = 0;
            $processedCount = 0;

            // Process orders in chunks of 100
            OrderMaster::where('payment_expires_at', '<', now())
                ->where('paymentStatus', 'pending')
                ->where(function ($query) {
                    $query->whereNotNull('stripesessionid')
                        ->orWhereNotNull('payment_link_id');
                })
                ->chunkById(100, function ($expiredOrders) use ($stripe, $mode, &$successCount, &$errorCount, &$processedCount, $totalExpired) {

                    foreach ($expiredOrders as $order) {
                        $processedCount++;

                        try {
                            $expired = false;

                            // Check clientType and handle accordingly
                            if ($order->clientType === 'customer' && !empty($order->stripesessionid) && $order->deviceType == "web") {
                                // For customer: Expire checkout session
                                $this->info("   Processing customer checkout session...");

                                $session = $stripe->checkout->sessions->expire(
                                    $order->stripesessionid,
                                    []
                                );

                                $expired = true;
                                Log::info('Stripe checkout session expired', [
                                    'order_id' => $order->code,
                                    'session_id' => $order->stripesessionid,
                                    'client_type' => 'customer',
                                    'mode' => $mode
                                ]);
                            } elseif ($order->clientType === 'customer' && !empty($order->stripesessionid) && $order->deviceType == "mobile") {
                                // For customer: Expire checkout session
                                $this->info("   Processing customer payment intent...");

                                $paymentIntent = $stripe->paymentIntents->retrieve($order->stripesessionid);

                                // Only cancel if in cancellable state
                                if (in_array($paymentIntent->status, ['requires_payment_method', 'requires_capture', 'requires_confirmation', 'requires_action'])) {
                                    $cancelledIntent = $stripe->paymentIntents->cancel($order->stripesessionid);
                                    $expired = true;

                                    Log::info('Stripe payment intent cancelled', [
                                        'order_id' => $order->id,
                                        'payment_intent_id' => $order->stripesessionid,
                                        'client_type' => 'customer',
                                        'mode' => $mode
                                    ]);
                                } else {
                                    $this->warn("   Payment Intent {$order->stripesessionid} status: {$paymentIntent->status} - Cannot cancel");
                                    $expired = true; // Still mark as processed
                                }

                            } elseif ($order->clientType === 'cashier' && !empty($order->payment_link_id)) {
                                // For cashier: Deactivate payment link
                                $this->info("   Processing cashier payment link...");

                                $paymentLink = $stripe->paymentLinks->update(
                                    $order->payment_link_id,
                                    ['active' => false]
                                );

                                $expired = true;
                                Log::info('Stripe payment link deactivated', [
                                    'order_id' => $order->code,
                                    'payment_link_id' => $order->payment_link_id,
                                    'client_type' => 'cashier',
                                    'mode' => $mode
                                ]);
                            }

                            if ($expired) {
                                // Update order status to expired
                                OrderMaster::where('id', $order->id)
                                    ->update(['paymentStatus' => 'expired']);

                                $successCount++;
                                $this->info("✓ [{$processedCount}/{$totalExpired}] Expired for Order ID: {$order->id} (ClientType: {$order->clientType})");
                            } else {
                                $errorCount++;
                                $this->warn("✗ [{$processedCount}/{$totalExpired}] Skipped Order ID: {$order->id} - No valid session/link found");
                            }
                        } catch (\Exception $e) {
                            // Session might be already expired or completed - log and continue
                            $errorCount++;
                            $this->warn("✗ [{$processedCount}/{$totalExpired}] Failed for Order ID: {$order->id} - {$e->getMessage()}");

                            Log::warning('Failed to expire Stripe session/payment link', [
                                'order_id' => $order->code,
                                'session_id' => $order->stripesessionid ?? null,
                                'payment_link_id' => $order->payment_link_id ?? null,
                                'client_type' => $order->clientType,
                                'error' => $e->getMessage(),
                                'mode' => $mode
                            ]);

                            // Still update the order status to expired in database
                            OrderMaster::where('id', $order->id)
                                ->update(['paymentStatus' => 'expired']);
                        }
                    }
                });

            $this->newLine();
            $this->info("Process completed!");
            $this->info("Total processed: {$processedCount}");
            $this->info("Successfully expired: {$successCount}");
            $this->info("Failed/Already expired: {$errorCount}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error during expiration process: {$e->getMessage()}");
            Log::error('Stripe session/payment link expiration command failed', [
                'error' => $e->getMessage()
            ]);
            return Command::FAILURE;
        }
    }
}
