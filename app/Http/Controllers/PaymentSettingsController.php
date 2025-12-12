<?php

namespace App\Http\Controllers;
use App\Models\PaymentSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{

    public function index()
    {
        $settings = PaymentSettings::first();
        return view('payment-settings.payment_settings',compact('settings'));
    }

    public function store(Request $request)
    {
        try {
              $request->validate([
                'payment_mode' => 'required|in:0,1',
                'test_secret_key' => 'required|string|max:20',
                'live_secret_key' => 'required|string|max:20',
                'test_client_id' => 'required|string|max:20',
                'live_client_id' => 'required|string|max:20',
                'webhook_secret_key' => 'required|string|max:20',
            ], [
                'payment_mode.required' => 'Please select a payment mode.',
                'payment_mode.in' => 'Invalid payment mode selected.',
                'test_secret_key.required' => 'Test Secret Key is required.',
                'live_secret_key.required' => 'Live Secret Key is required.',
                'test_client_id.required' => 'Test Client ID is required.',
                'live_client_id.required' => 'Live Client ID is required.',
                'webhook_secret_key.required' => 'Webhook Secret Key is required.',
            ]);

            $existing = PaymentSettings::where('payment_gateway', 'stripe')->first();

            if ($existing) {
                $existing->update([
                    'payment_mode' => $request->payment_mode,
                    'test_secret_key' => $request->test_secret_key,
                    'live_secret_key' => $request->live_secret_key,
                    'test_client_id' => $request->test_client_id,
                    'live_client_id' => $request->live_client_id,
                    'webhook_secret_key' => $request->webhook_secret_key
                ]);

                return back()->with('success', 'Payment settings updated successfully!');
            }

            PaymentSettings::create([
                'payment_gateway' => "stripe",
                'payment_mode' => $request->payment_mode,
                'test_secret_key' => $request->test_secret_key,
                'live_secret_key' => $request->live_secret_key,
                'test_client_id' => $request->test_client_id,
                'live_client_id' => $request->live_client_id,
                'webhook_secret_key' => $request->webhook_secret_key
            ]);

            return redirect()->back()->with('success', 'Payment settings saved successfully.');
        } catch (\Exception $e) {
            \Log::error('PaymentSettings Store Error: '.$e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while saving payment settings: ' . $e->getMessage());
        }
    }

}
