<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SMSLog;
use App\Classes\Twilio;
use Illuminate\Support\Facades\Log;

class SmsSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:send-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending SMS messages using Twilio';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to send pending SMS messages...');

        // Get total count of pending SMS
        $totalPending = SMSLog::where('status', 'pending')->count();

        if ($totalPending === 0) {
            $this->info('No pending SMS messages found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$totalPending} pending SMS messages.");
        $this->info("Processing in chunks of 100...");

        $twilio = new Twilio();
        $successCount = 0;
        $failedCount = 0;
        $processedCount = 0;

        // Process in chunks of 100
        SMSLog::where('status', 'pending')
            ->chunkById(100, function ($pendingSms) use ($twilio, &$successCount, &$failedCount, &$processedCount, $totalPending) {

                $this->newLine();
                $this->info("Processing chunk: " . ($processedCount + 1) . " to " . ($processedCount + $pendingSms->count()));

                foreach ($pendingSms as $sms) {
                    $processedCount++;
                    $this->line("[{$processedCount}/{$totalPending}] Sending SMS to {$sms->mobile_number}...");

                    try {
                        // Send SMS using Twilio
                        $response = $twilio->sendMessage($sms->template_message, $sms->mobile_number);

                        if ($response) {
                            // Update SMS log as sent
                            $sms->update([
                                'status' => 'sent',
                                'sent_at' => now(),
                                'message_response' => json_encode([
                                    'sid' => $response->sid,
                                    'status' => $response->status,
                                    'date_sent' => $response->dateSent,
                                ])
                            ]);

                            $this->info("✓ SMS sent successfully to {$sms->mobile_number}");
                            $successCount++;
                        } else {
                            // Update SMS log as failed
                            $sms->update([
                                'status' => 'failed',
                                'message_response' => json_encode(['error' => 'Twilio sendMessage returned false'])
                            ]);

                            $this->error("✗ Failed to send SMS to {$sms->mobile_number}");
                            $failedCount++;
                        }
                    } catch (\Exception $e) {
                        // Update SMS log as failed with error
                        $sms->update([
                            'status' => 'failed',
                            'message_response' => json_encode([
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ])
                        ]);

                        $this->error("✗ Error sending SMS to {$sms->mobile_number}: {$e->getMessage()}");
                        $failedCount++;

                        Log::error('Console SMS Send Failed', [
                            'sms_log_id' => $sms->id,
                            'mobile_number' => $sms->mobile_number,
                            'error' => $e->getMessage()
                        ]);
                    }

                    // Small delay to avoid rate limiting
                    usleep(100000); // 0.1 second delay
                }

                $this->info("Chunk completed. Progress: {$processedCount}/{$totalPending}");
            });

        $this->newLine();
        $this->info("SMS sending completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Success', $successCount],
                ['Failed', $failedCount],
                ['Total', $totalPending]
            ]
        );

        return Command::SUCCESS;
    }
}
