<?php

namespace App\Classes;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use App\Models\TwilioSetting;

class Twilio
{
    protected function getConfig()
    {
        // Get active Twilio settings from DB
        $settings = TwilioSetting::getActiveSettings();

        if (!$settings) {
            Log::error("No active Twilio settings found in database.");
            return null;
        }

        return [
            'mode' => $settings->twilio_mode,
            'sid'   => $settings->twilio_session_id,
            'token' => $settings->twilio_auth_id,
            'from'  => $settings->twilio_number,
        ];
    }

    public function isLive()
    {
        $config = $this->getConfig();
        return $config && strtoupper($config['mode']) === 'LIVE';
    }

    public function sendMessage($message, $recipient)
    {
        $config = $this->getConfig();

        Log::info('Twilio SMS Attempt', [
            'mode'     => $config['mode'],
            'from'     => $config['from'],
            'to'       => $recipient,
            'sid_used' => $config['sid'],
            'message'  => $message
        ]);

        try {
            $client = new Client($config['sid'], $config['token']);

            $response = $client->messages->create($recipient, [
                'from' => $config['from'],
                'body' => $message
            ]);

            Log::info('Twilio SMS Success', [
                'sid'    => $response->sid,
                'status' => $response->status,
                'to'     => $recipient
            ]);

            return $response;
        } catch (\Exception $e) {

            Log::error('Twilio SMS Failed', [
                'error' => $e->getMessage(),
                'to'    => $recipient,
                'mode'  => $config['mode']
            ]);

            return false;
        }
    }
}
