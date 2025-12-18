<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Classes\FirebaseNotification;
use App\Models\Storelocation;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\ApiModel;
use App\Models\Users;
use App\Models\Customer;
use App\Models\OrderMaster;
use App\Models\OrderLineEntries;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

use Stripe\WebhookEndpoint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use App\Models\Dips;
use App\Models\Pizzas;
use App\Models\Softdrinks;
use App\Models\SidesMaster;
use App\Models\Specialoffer;
use App\Models\SignaturePizza;
use App\Classes\Stripe;
use App\Services\DoorDashService;
use App\Models\SmsTemplate;
use App\Models\DoorDashStep;
use App\Classes\Twilio;
use App\Models\Business;


class WebhookController extends Controller
{
    public function __construct(GlobalModel $model, ApiModel $apimodel)
    {
        $this->model = $model;
        $this->apimodel = $apimodel;
    }
    public function webhook(Request $r)
    {
        try {

            $paymentSettings = DB::table('payment_settings')->first();

            if (!$paymentSettings) {
                Log::warning("Payment setting not found");
                return response()->json(["status" => 200, "message" => "'Payment settings not found in database"]);
            }

            // Determine mode: 0 = sandbox, 1 = live
            $mode = $paymentSettings->payment_mode == 1 ? 'LIVE' : 'SANDBOX';

            // Set secret key based on mode
            $endpoint_secret = $mode === 'LIVE'
                ? $paymentSettings->webhook_secret_live_key
                : $paymentSettings->webhook_secret_key;


            // Get raw payload for signature verification
            $payload = $r->getContent();
            $sig_header = $r->header('Stripe-Signature');

            // Verify webhook signature
            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload,
                    $sig_header,
                    $endpoint_secret
                );
            } catch (\UnexpectedValueException $e) {
                return response()->json(['error' => 'Invalid payload'], 400);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            Log::info("payment web hook " . json_encode($event));

            // Access the data
            $paymentObject = $event->data->object;
            $eventType = $event->type;

            // Check object type exists
            if (!isset($paymentObject->object)) {
                Log::warning("Object type not found in webhook");
                return response()->json(["status" => 200, "message" => "Invalid object type."]);
            }

            $objectType = $paymentObject->object;

            // HANDLE CHECKOUT SESSION EVENTS
            if ($objectType == "checkout.session") {
                $stripeSessionId = $paymentObject->id;
                $paymentIntentId = $paymentObject->payment_intent ?? null;
                $paymentStatus = $paymentObject->payment_status ?? null;
                $paymentLinkId = $paymentObject->payment_link ?? null;

                Log::info("Processing checkout.session - Session ID: " . $stripeSessionId . " | Payment Status: " . $paymentStatus);

                // Find order by session ID
                $result = DB::table("ordermaster")
                    ->select("ordermaster.*")
                    ->where("stripesessionid", $stripeSessionId)
                    ->first();

                if (!empty($result)) {
                    if ($result->orderStatus === 'pending') {
                        $data = [];
                        $data['webHookResponse'] = json_encode($event);

                        if ($paymentIntentId) {
                            $data['paymentOrderId'] = $paymentIntentId;
                        }

                        // Handle completed session with paid status
                        if ($eventType == "checkout.session.completed" && $paymentStatus == "paid") {
                            $data['orderStatus'] = 'placed';
                            $data['paymentStatus'] = "paid";
                            $this->model->doEdit($data, 'ordermaster', $result->code);

                            // ✅ Deactivate payment link to prevent reuse
                            if ($paymentLinkId) {
                                $this->deactivatePaymentLink($paymentLinkId, $paymentSettings, $mode);
                            }

                            Log::info("✅ ORDER PAID - Status Updated", [
                                'order_code' => $result->code,
                                'session_id' => $stripeSessionId,
                                'payment_link_id' => $paymentLinkId
                            ]);

                            // ✅ If delivery order, accept DoorDash quote
                            if (strtolower($result->deliveryType ?? '') === 'delivery') {
                                $this->handleDoorDashDelivery($result->code);
                            }
                             // ✅ Send socket notification
                            $this->sendSocketNotification($result);
                        }
                        // Handle expired session
                        elseif ($eventType == "checkout.session.expired") {
                            $data['orderStatus'] = 'pending';
                            $data['paymentStatus'] = "failed";
                            $this->model->doEdit($data, 'ordermaster', $result->code);

                            Log::info("Order canceled: " . $stripeSessionId);
                        }
                        // Handle unpaid status
                        elseif ($paymentStatus == "unpaid") {
                            $data['orderStatus'] = 'cancelled';
                            $data['paymentStatus'] = "cancelled";
                            $this->model->doEdit($data, 'ordermaster', $result->code);

                            Log::info("Order marked unpaid: " . $stripeSessionId);
                        }
                    } else {
                        Log::info("Order already processed: " . $stripeSessionId);
                    }
                } else {
                    Log::warning("Order not found for session: " . $stripeSessionId);
                }
            }

            // HANDLE PAYMENT INTENT EVENTS
            elseif ($objectType == "payment_intent") {
                $paymentIntentId = $paymentObject->id;
                $status = $paymentObject->status ?? null;
                $customer = $paymentObject->customer ?? null;

                Log::info("Processing payment_intent - ID: " . $paymentIntentId . " | Status: " . $status);

                // Find order by payment intent ID
                $result = DB::table("ordermaster")
                    ->select("ordermaster.*")
                    ->where("stripesessionid", $paymentIntentId)
                    ->first();

                if (!empty($result)) {
                    if ($result->orderStatus === 'pending') {
                        $data = [];
                        $data['webHookResponse'] = json_encode($event);

                        // Handle succeeded payment intent
                        if ($eventType == "payment_intent.succeeded") {
                            $data['orderStatus'] = 'placed';
                            $data['paymentStatus'] = "paid";
                            $this->model->doEdit($data, 'ordermaster', $result->code);

                            // ✅ If delivery order, accept DoorDash quote
                            if (strtolower($result->deliveryType ?? '') === 'delivery') {
                                $this->handleDoorDashDelivery($result->code);
                            }

                             // ✅ Send socket notification
                            $this->sendSocketNotification($result);

                            Log::info("Order updated to paid via payment_intent: " . $paymentIntentId);
                        }
                        // Handle failed payment
                        elseif ($eventType == "payment_intent.payment_failed") {

                            $data['orderStatus'] = 'pending';
                            $data['paymentStatus'] = "failed";
                            $this->model->doEdit($data, 'ordermaster', $result->code);


                            Log::info("Order canceled via payment_intent: " . $paymentIntentId);
                        } // Handle created payment intent (optional - just for tracking)
                        elseif ($eventType == "payment_intent.created") {

                            Log::info("Payment intent created: " . $paymentIntentId);
                        }
                    } else {
                        Log::info("Order already processed: " . $paymentIntentId);
                    }
                } else {
                    Log::warning("Order not found for payment intent: " . $paymentIntentId);
                }
            }


            // HANDLE PAYMENT LINK EVENTS
            elseif ($objectType == "payment_link") {
                $paymentLinkId = $paymentObject->id;
                $paymentLinkUrl = $paymentObject->url ?? null;
                $isActive = $paymentObject->active ?? null;
                $metadata = $paymentObject->metadata ?? [];
                $orderId = $metadata['order_id'] ?? null;

                Log::info("Processing payment_link - ID: " . $paymentLinkId . " | Event: " . $eventType . " | Order ID: " . $orderId);

                // Find order by payment link ID
                $result = DB::table("ordermaster")
                    ->select("ordermaster.*")
                    ->where("payment_link_id", $paymentLinkId)
                    ->first();

                if (!empty($result)) {
                    if ($result->paymentStatus === 'pending') {
                        $data = [];
                        $data['webHookResponse'] = json_encode($event);

                        // Handle payment link created
                        if ($eventType == "payment_link.created") {
                            Log::info("Payment link created for order: " . $result->code);
                        }
                        // Handle payment link updated (payment completed)
                        elseif ($eventType == "payment_link.updated") {
                            // Check if payment was completed (link becomes inactive after successful payment)
                            if ($isActive === false) {
                                $data['orderStatus'] = 'placed';
                                $data['paymentStatus'] = "paid";
                                $this->model->doEdit($data, 'ordermaster', $result->code);


                                // ✅ Deactivate payment link to prevent reuse
                                if ($paymentLinkUrl) {
                                    $this->deactivatePaymentLink($paymentLinkUrl, $paymentSettings, $mode);
                                }

                                Log::info("✅ ORDER PAID - Status Updated", [
                                    'order_code' => $result->code,
                                    'session_id' =>  $result->stripesessionid,
                                    'payment_link_id' => $paymentLinkId
                                ]);

                                Log::info("Order Status " . $result->deliveryType);
                                // ✅ If delivery order, accept DoorDash quote
                                if (strtolower($result->deliveryType ?? '') === 'delivery') {
                                    $this->handleDoorDashDelivery($result->code);
                                }

                                 // ✅ Send socket notification
                                $this->sendSocketNotification($result);

                                Log::info("Order updated to paid via payment_link: " . $paymentLinkId);
                            } else {
                                Log::info("Payment link updated for order: " . $result->code);
                            }
                        }
                    } else {
                        Log::info("Order already processed: " . $paymentLinkId);
                    }
                } else {
                    Log::info("Payment link event received but order not found or not linked yet: " . $paymentLinkId);
                }
            }


            // Unknown object type
            else {
                Log::info("Unhandled object type: " . $objectType . " | Event: " . $eventType);
            }

            return response()->json(["status" => 200, "message" => "Webhook processed successfully."]);
        } catch (\Exception $ex) {
            Log::error("Webhook error: " . $ex->getMessage());
            // Return 200 to prevent Stripe from retrying
            return response()->json(["status" => 200, "message" => "Webhook received."]);
        }
    }


    /**
     * Helper: Deactivate Payment Link after successful payment
     */
    private function deactivatePaymentLink($paymentLinkId, $paymentSettings, $mode)
    {
        try {
            $secretKey = $mode === 'LIVE'
                ? $paymentSettings->live_secret_key
                : $paymentSettings->test_secret_key;

            \Stripe\Stripe::setApiKey($secretKey);

            \Stripe\PaymentLink::update($paymentLinkId, ['active' => false]);

            Log::info("✅ Payment link deactivated", ['payment_link_id' => $paymentLinkId]);
        } catch (\Exception $e) {
            Log::error("Failed to deactivate payment link", [
                'payment_link_id' => $paymentLinkId,
                'error' => $e->getMessage()
            ]);
        }
    }


    //handle doordash accespt quote successful payment
    private function handleDoorDashDelivery($orderCode)
    {
        try {
            Log::info("Starting DoorDash delivery process", ['order_code' => $orderCode]);

            // Get full order details
            $order = OrderMaster::where('code', $orderCode)->first();

            if (!$order) {
                Log::error("Order not found for DoorDash delivery", ['order_code' => $orderCode]);
                return;
            }

            // Initialize DoorDash service
            $doorDash = new DoorDashService();

            // Accept the quote (if quote was already created)
            if (!empty($order->doordash_delivery_id)) {
                Log::info("Accepting DoorDash quote", [
                    'order_code' => $orderCode,
                    'quote_id' => $order->doordash_quote_id
                ]);

                $response = $doorDash->acceptQuote($order->doordash_delivery_id);

                if ($response['success']) {
                    // Update order with delivery details
                    $deliveryData = $response['data'] ?? [];

                    OrderMaster::where('code', $orderCode)->update([
                        'doordash_delivery_id' => $deliveryData['external_delivery_id'] ?? null,
                        'doordash_status' => 'QUOTE_ACCEPTED',
                        'doordash_accept_response' => json_encode($response)
                    ]);

                    DoorDashStep::create([
                        'order_id' => $orderCode,
                        'doordash_status' => 'QUOTE_ACCEPTED',
                        'doordash_delivery_id' =>$deliveryData['external_delivery_id'],
                        'doordash_response' => json_encode($response),
                    ]);

                    $this->sendDoorDashTrackingSMS($order, $deliveryData);


                    Log::info("✅ DoorDash quote accepted successfully", [
                        'order_code' => $orderCode,
                        'delivery_id' => $deliveryData['external_delivery_id'] ?? null,
                        'status' => $deliveryData['delivery_status'] ?? 'QUOTE_ACCEPTED'
                    ]);
                } else {
                    Log::error("Failed to accept DoorDash quote", [
                        'order_code' => $orderCode,
                        'error' => $response['error'] ?? 'Unknown error',
                        'response' => $response
                    ]);
                }
            } else {
                Log::warning("No DoorDash quote ID found for order", [
                    'order_code' => $orderCode,
                    'message' => 'Quote should be created before payment'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("DoorDash delivery handling failed", [
                'order_code' => $orderCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Send DoorDash tracking SMS
    private function sendDoorDashTrackingSMS($order, $deliveryData)
    {
        try {
            // Get SMS template (assuming template ID 8 for DoorDash tracking)
            $smsTemplate = SmsTemplate::where("id", 8)->first();

            if (!$smsTemplate) {
                Log::warning("DoorDash SMS template not found", ['template_id' => 8]);
                return;
            }

            // Extract tracking URL and estimated delivery time
            $trackingUrl = $deliveryData['tracking_url'] ?? '';

            // Replace placeholders in template
            $message = str_replace(
                ['{order_number}', '{tracking_url}'],
                [$order->code, $trackingUrl],
                $smsTemplate->template
            );

            $twilio = new Twilio;

            // Send SMS if in LIVE mode
            if ($twilio->isLive() && !empty($order->mobileNumber)) {
                $sms = $twilio->sendMessage($message, $order->mobileNumber);

                Log::info("DoorDash tracking SMS sent", [
                    'order_code' => $order->code,
                    'phone' => $order->mobileNumber,
                    'tracking_url' => $trackingUrl
                ]);
            } else {
                Log::info("DoorDash tracking SMS (test mode)", [
                    'order_code' => $order->code,
                    'message' => $message,
                    'tracking_url' => $trackingUrl
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send DoorDash tracking SMS", [
                'order_code' => $order->code,
                'error' => $e->getMessage()
            ]);
        }
    }


    /**
     * Send socket notification for order tracking
     *
     * @param object $order Order details from ordermaster table
     * @return void
     */
    private function sendSocketNotification($order)
    {
        try {
            $socketUrl = env('SOCKET_URL', 'https://pizzatracking.neosao.online');

            $params = [
                'orderNumber' => $order->id,
                'phoneNumber' => $order->mobileNumber,
                'status' => 'placed',
                'storeCode' => $order->storeLocation ?? '',
                'deliveryType' => strtolower($order->deliveryType)
            ];

            $url = $socketUrl . '/order/forward?' . http_build_query($params);

            Log::info("Sending socket notification", [
                'url' => $url,
                'params' => $params
            ]);

            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                Log::info("Socket notification sent successfully", [
                    'order_code' => $order->code,
                    'response' => $response->body()
                ]);
            } else {
                Log::warning("Socket notification failed", [
                    'order_code' => $order->code,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Socket notification error: " . $e->getMessage(), [
                'order_code' => $order->code ?? 'unknown'
            ]);
        }
    }

    public function payment_success(Request $r)
    {
        return response()->json(["status" => 200, "message" => "Payment Successfull."]);
    }

    public function payment_failed(Request $r)
    {
        return response()->json(["status" => 300, "message" => "Payment Failed."]);
    }

    public function payment_cancel(Request $r)
    {
        return response()->json(["status" => 200, "message" => "Payment Successfull."]);
    }


    //doordash webhook
    public function doordash_webhook(Request $r)
    {
        try {
            Log::info('DoorDash webhook received', [
                'headers' => $r->headers->all(),
                'payload' => $r->all(),
            ]);

            $payload = $r->all();
            $eventName = $payload['event_name'] ?? null;
            $externalDeliveryId = $payload['external_delivery_id'] ?? null;

            if (!$eventName || !$externalDeliveryId) {
                Log::warning('DoorDash webhook missing required fields');
                return response()->json(['message' => 'Invalid webhook data'], 400);
            }

            // Find order by DoorDash delivery ID
            $order = OrderMaster::where('doordash_delivery_id', $externalDeliveryId)->first();

            if (!$order) {
                Log::warning('Order not found for DoorDash delivery', [
                    'external_delivery_id' => $externalDeliveryId,
                    'event_name' => $eventName
                ]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            $statusMap = [
                'DASHER_PICKED_UP' => 'pickup',
                'DASHER_DROPPED_OFF'               => 'delivered',
                'DELIVERY_CANCELLED'               => 'cancelled',
            ];

            $updateData = [
                'doordash_status' => $eventName
            ];

            if (isset($statusMap[$eventName])) {
                $updateData['orderStatus'] = $statusMap[$eventName];
            }

            OrderMaster::where('code', $order->code)->update($updateData);


            // Create DoorDashStep record
            DoorDashStep::create([
                'order_id' => $order->code,
                'doordash_status' => $eventName,
                'doordash_delivery_id' => $externalDeliveryId,
                'doordash_response' => json_encode($payload),
            ]);

            Log::info('DoorDash webhook processed successfully', [
                'order_code' => $order->code,
                'event_name' => $eventName
            ]);

            return response()->json(['message' => 'Webhook received successfully'], 200);
        } catch (\Exception $e) {
            Log::error('DoorDash webhook error', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }
}
