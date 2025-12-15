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
use App\Models\SmsTemplate;
use App\Services\DoorDashService;
use App\Models\DoorDashStep;
use App\Models\Business;
use App\Classes\Twilio;

class CustomerOrderController extends Controller
{
    protected DoorDashService $doorDashService;
    public function __construct(GlobalModel $model, ApiModel $apimodel, DoorDashService $doorDashService)
    {
        $this->model = $model;
        $this->apimodel = $apimodel;
        $this->doorDashService = $doorDashService;
    }

    public function send_notification($storeLocation, $orderCode)
    {
        $firebase_tokens = [];

        $teleCashiers = DB::table("usermaster")
            ->where('role', 'R_4')
            ->where("isActive", 1)
            ->get();
        foreach ($teleCashiers as $user) {
            if ($user->firebase_id != "" && $user->firebase_id != null) {
                array_push($firebase_tokens, $user->firebase_id);
            }
        }

        // all cashiers except login user
        $users = DB::table("usermaster")
            ->where('storeLocationCode', $storeLocation)
            ->where('role', 'R_3')
            ->where("isActive", 1)
            ->get();
        if ($users) {
            foreach ($users as $user) {
                if ($user->firebase_id != "" && $user->firebase_id != null) {
                    array_push($firebase_tokens, $user->firebase_id);
                }
            }
        }

        if (!empty($firebase_tokens)) {
            $title = "Order Placed " . date("Y-m-d h:i a");
            $message = "#" . $orderCode . " order place on " . $storeLocation;
            $random = rand(1, 9999);
            $dataArr = $notification = array();
            $dataArr['device_id'] = $firebase_tokens;
            $dataArr['message'] = $message;
            $dataArr['title'] = $title;
            $dataArr['random_id'] = $random;
            $notification['device_id'] = $firebase_tokens;
            $notification['message'] = $message;
            $notification['title'] = $title;
            $notification['random_id'] = $random;
            $fbNotification = new FirebaseNotification();
            $res = $fbNotification->sendNotification($dataArr, $notification);
        }
    }


    public function order_place(Request $r)
    {
        try {
            $storeCode = '';
            $userAgent = $r->header('User-Agent');

            if ($r->storeCode && $r->storeCode != "") {
                $storeCode = $r->storeCode;
            } else {

                $zipCodeEntry = DB::table('zipcode')->where('zipcode', $r->zipCode)
                    ->where('isActive', 1)
                    ->first();
                if ($zipCodeEntry) {
                    $storeCode = $zipCodeEntry->storeCode;
                }
            }

            $store = DB::table('storelocation')
                ->select("storelocation.timezone", "storelocation.storeAddress", "storelocation.storeLocation", "storelocation.pickup_number")
                ->where('code', $storeCode)
                ->first();

            if (!empty($store)) {
                $timezone = $store->timezone;
                Carbon::now()->timezone($timezone);
                date_default_timezone_set($timezone);
            }
            $currentdate = Carbon::now();
            $now =  $currentdate->toDateTimeString();
            $paymentMode = env('PAYMENT_MODE', 'SANDBOX');
            $input = $r->all();
            $rules = [
                'customerCode'           => 'nullable',
                'mobileNumber'           => ['required', 'numeric'],
                'deliveryType'           => 'required',
                'products'             => 'required|array|min:1',
                'products.*.id'             => 'required',
                'products.*.productCode'    => 'required',
                'products.*.productName'    => 'required',
                'products.*.productType'    => 'required',
                //'products.*.config'         => 'required',
                'products.*.quantity'       => 'required',
                'products.*.price'          => 'required',
                'products.*.amount'         => 'required',
                'subTotal'             => 'required',
                'discountAmount'         => 'nullable',
                'taxPer'             => 'nullable',
                'taxAmount'           => 'nullable',
                'deliveryCharges'         => 'nullable',
                'extraDeliveryCharges'       => 'nullable',
                'grandTotal'           => 'required',
                'deviceType'           => 'nullable'
                //'deliveryExecutive'       => 'required',

            ];

            $messages = [
                //'customerCode.required' 			=> 'Customer is required',
                'mobileNumber.required'         => 'Phone number is required',
                'mobileNumber.numeric'           => 'Phone number is invalid',
                'deliveryType.required'         => 'Delivery Type is required',
                //'deliveryType.in' 			        => 'Delivery Type must be delivery',
                'products.required'             => 'Cart is empty, cannot place the order',
                'products.array'             => 'Cart is Invalid',
                'products.min'                 => 'Cart must have at-least one product/item',
                'subTotal.required'           => 'Subtotal is missing',
                'grandTotal.required'           => 'Grand total is missing',
                'products.*.id.required'            => 'Item/Product Id is missing',
                'products.*.productCode.required'   => 'Item/Product Product Code is missing',
                'products.*.productName.required'   => 'Item/Product Product Name is missing',
                'products.*.productType.required'   => 'Item/Product Product Type is missing',
                //'products.*.config.required'        => 'Item/Product Configuration is missing',
                'products.*.quantity.required'      => 'Item/Product Qunatity is missing',
                'products.*.price.required'         => 'Item/Product Price is missing',
                'products.*.amount.required'        => 'Item/Product Amount is missing',
                'address.required'                  => 'Address is required',
                'address.min'                       => "Incomplete address",
                'address.max'                       => "Maximum limit for address is reached",
                'zipCode.required'                  => "Postal Code is required",
                'zipCode.regex'                     => "Enter valid Postal Code.",
                'deliveryExecutive.required'        => "Delivery Executive is required",
                'customerName.required'             => "Customer name is required",
                'customerName.min'                  => "Minimum 3 characters are required for customer name",
                'customerName.max'                  => "Maximum limit reached for customer name",
                'customerName.regex'                => "Invalid customer name",
            ];

            if ($r->deliveryType != "pickup") {

                $rules['address'] = 'required|min:10|max:400';
                $rules['zipCode'] = 'required|regex:/^[ABCEGHJKLMNPRSTVXY]\d[A-Z]\d[A-Z]\d$/i';
                $rules['customerName'] = 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/';

                $messages['address.required'] = "Address is required";
                $messages['address.min'] = "Incomplete address";
                $messages['address.max'] = "Maximum limit for address is reached";
                $messages['zipCode.required'] = "Postal Code is required";
                $messages['zipCode.regex'] = "Enter Valid Postal Code.";
                $messages['customerName.required'] = "Customer name is required";
                $messages['customerName.min'] = "Minimum 3 characters are required for customer name";
                $messages['customerName.max'] = "Maximum limit reached for customer name";
                $messages['customerName.regex'] = "Invalid customer name";
            } else {
                $rules['storeCode'] = 'required';
                $rules['customerName'] = 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/';
                $messages['customerName.min'] = "Minimum 3 characters are required for customer name";
                $messages['customerName.max'] = "Maximum limit reached for customer name";
                $messages['customerName.regex'] = "Invalid customer name";
                $messages['storeCode.required'] = "Store Location is required";
            }

            $validator = Validator::make($input, $rules, $messages);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            if ($r->customerCode != "") {
                $customerCode = $r->customerCode;
            } else {
                $customer = Customer::where('mobileNumber', $r->mobileNumber)->first();
                if ($customer) {
                    $customerCode = $customer->code;
                } else {
                    $customerData = [
                        "fullName" => $r->customerName,
                        "mobileNumber" => $r->mobileNumber,
                        "isActive" => 1,
                        "isDelete" => 0,
                        "created_at" => $now,
                    ];
                    $customer = $this->model->addNew($customerData, "customer", 'CST');
                    if ($customer) {
                        $customerCode = $customer;
                    } else {
                        return response()->json(["message" => "Failed to place order while creating customer's data."], 400);
                    }
                }
            }

            if ($this->isStoreOpen($storeCode)) { // Developer: Shreyas Mahamuni - Start, Working Date: 20-12-2023

                $delvCode = "";

                $defaultDeliveryExecutive = DB::table('usermaster')
                    ->where('role', 'R_2')
                    ->where('isActive', 1)
                    ->where('defaultDeliveryExecutive', 1)
                    ->where('storeLocationCode', $storeCode)
                    ->first();
                if ($defaultDeliveryExecutive) {
                    $delvCode = $defaultDeliveryExecutive->code;
                } else {
                    $defaultDeliveryExecutive = DB::table('usermaster')
                        ->where('role', 'R_2')
                        ->where('isActive', 1)
                        ->where('storeLocationCode', $storeCode)
                        ->first();
                    if ($defaultDeliveryExecutive) {
                        $delvCode = $defaultDeliveryExecutive->code;
                    }
                }

                $data = [
                    "customerCode" => $customerCode,
                    "customerName" => $r->customerName,
                    "mobileNumber" => $r->mobileNumber,
                    "address" => $r->address,
                    "deliveryType" => $r->deliveryType,
                    "created_at" => $now,
                    "orderDate" => $now,
                    "addID" => $customerCode,
                    "clientType" => 'customer',
                    "subTotal" => $r->subTotal,
                    "discountAmount" => $r->discountAmount,
                    "discountPer" => $r->discountPer,
                    "taxAmount" => $r->taxAmount,
                    "taxPer" => $r->taxPer,
                    "grandTotal" => $r->grandTotal,
                    "deliveryCharges" => $r->deliveryCharges,
                    "deliveryExecutiveCode" => $delvCode,
                    "extraDeliveryCharges" => $r->extraDeliveryCharges,
                    "transactionDate" => $now,
                    "orderStatus" => "pending", //previous status was placed
                    "paymentStatus" => "pending",
                    "orderFrom" => "online",
                    "zipCode" => $r->zipCode,
                    "storeLocation" => $storeCode
                ];
                // Developer: Shreyas Mahamuni - Start
                $orderCode = 1;
                $curDate = date('Y-m-d H:i:00');
                if ($curDate < date("Y-m-d 04:30:00")) {
                    $prevDate = date("Y-m-d 04:30:00", strtotime("- 1 day"));
                    $endDate = date("Y-m-d 04:29:59");
                    $records = DB::table("ordermaster")->whereBetween('created_at', [$prevDate, $endDate])->count();
                    if ($records > 0) {
                        $orderCode = $records + 1;
                    }
                } else {
                    $prevDate = date("Y-m-d 04:30:00");
                    $endDate = date("Y-m-d 04:29:59", strtotime("+ 1 day"));
                    $records = DB::table("ordermaster")->whereBetween('created_at', [$prevDate, $endDate])->count();
                    if ($records > 0) {
                        $orderCode = $records + 1;
                    }
                }
                // Developer: Shreyas Mahamuni - End
                $order = $this->model->addNew($data, "ordermaster", 'ORD');
                if ($order) {
                    $str = explode('_', $order);
                    $id = $str[1];
                    $txnId = "SPO" . date('Ymd') . $id;
                    $receiptNo = date('Ymd') . $id;
                    $result = OrderMaster::where("code", $order)->update(["txnId" => $txnId, "orderCode" => $orderCode]);
                    if ($result == true) {
                        foreach ($r->products as $item) {
                            $orderLine = new OrderLineEntries;
                            $orderLine->code = Str::uuid();
                            $orderLine->pid = $item["id"];
                            $orderLine->orderCode = $order;
                            $orderLine->productCode = $item["productCode"];
                            $orderLine->productName = $item["productName"];
                            $orderLine->productType = $item["productType"];
                            $orderLine->config = json_encode($item["config"]);
                            $orderLine->quantity = $item["quantity"];
                            $orderLine->price = $item["price"];
                            $orderLine->amount = $item["amount"];
                            $orderLine->pizzaSize = $item["pizzaSize"];
                            $orderLine->comments = $item["comments"] ?? "";
                            $orderLine->created_at = $now;
                            $orderLine->pizzaPrice = $item['pizzaPrice'] ?? "0.00";
                            $orderLine->save();
                        }

                        // $uri = config('constant.SITE_MODE') == 'LIVE' ? config('constant.SOCKET_URL_LIVE') : config('constant.SOCKET_URL_TEST');
                        // $uri .= '/order/place/client';
                        // $response = Http::get($uri, [
                        //   'orderCode' => $order,
                        //   'orderNumber' =>  $orderCode,
                        //   'phoneNumber' => $r->mobileNumber,
                        //   'status' => "pending",
                        //   'storeCode' => $storeCode,
                        //   'deliveryType' => $r->deliveryType,
                        //   "customerName" => $r->customerName,
                        //   "grandTotal" => $r->grandTotal,
                        //   "orderFrom" => "online"
                        // ]);


                        //sending sms

                        if ($r->deliveryType == "delivery") {
                            $smsTemplate = SmsTemplate::where("id", 4)->first();

                            // Get current time and add 20 minutes for estimated delivery
                            $deliveryTime = now()->addMinutes(20)->format('h:i A');

                            // Replace placeholders in template with actual values
                            $message = str_replace(
                                ['{order_number}', '{delivery_time}'],
                                [$order, $deliveryTime],
                                $smsTemplate->template
                            );

                            $twilio = new Twilio;

                            if ($twilio->isLive()) {
                                $sms = $twilio->sendMessage($message, $r->mobileNumber);
                            }


                        }

                        if ($r->deliveryType == "pickup") {
                            $smsTemplate = SmsTemplate::where("id", 3)->first(); // pickup template

                            // Pickup time = now + 15 minutes (change if needed)
                            $pickupTime = now()->addMinutes(20)->format('h:i A');

                            // Example store address
                            $storeAddress = $store->storeAddress; // replace or fetch from DB

                            // Replace placeholders
                            $message = str_replace(
                                ['{order_number}', '{pickup_time}', '{store_address}'],
                                [$order, $pickupTime, $storeAddress],
                                $smsTemplate->template
                            );

                            $twilio = new Twilio;

                            if ($twilio->isLive()) {
                                $sms = $twilio->sendMessage($message, $r->mobileNumber);
                            }
                        }

                        //create doordash api
                        if ($r->deliveryType == "delivery") {
                            $businessId = "";
                            $business = Business::first();
                            if ($business) {
                                $businessId = $business->external_business_id;
                            }

                            $quotePayload = [
                                'external_delivery_id' => $order,
                                //'pickup_address' => $r->storeAddress,
                                'pickup_address' => "901 Market Street 6th Floor San Francisco, CA 94103",
                                'pickup_phone_number' => '+12345678900',
                                'dropoff_address' => $r->address,
                                'dropoff_phone_number' => $r->mobileNumber,
                                'dropoff_contact_given_name' => $r->customerName,
                                "pickup_external_business_id" => $businessId,
                                "pickup_external_store_id" => $storeCode,
                                'order_value' => (int)round($r->grandTotal * 100), // Convert to cents
                                'currency' => 'CAD',
                            ];


                            $doorDashResult = $this->doorDashService->makeRequest('post', '/quotes', $quotePayload);

                            Log::info('DoorDash Create Quote response', $doorDashResult);

                            // Check if DoorDash request was successful
                            if (!isset($doorDashResult['success']) || $doorDashResult['success'] !== true) {
                                // Log the error
                                Log::error('DoorDash quote creation failed', [
                                    'order_code' => $order,
                                    'error' => $doorDashResult['error'] ?? 'Unknown error',
                                    'data' => $doorDashResult['data'] ?? null
                                ]);


                                $errorMessage = '';

                                if (
                                    isset($doorDashResult['data']['field_errors'][0]['error'])
                                ) {
                                    $errorMessage = $doorDashResult['data']['field_errors'][0]['error'];
                                } elseif (
                                    isset($doorDashResult['data']['message'])
                                ) {
                                    $errorMessage = $doorDashResult['data']['message'];
                                }

                                DoorDashStep::create([
                                    'order_id' => $order,
                                    'doordash_status' => 'QUOTE_FAILED',
                                    'doordash_delivery_id' => null,
                                    'doordash_response' => json_encode($doorDashResult),
                                ]);

                                return response()->json([
                                    "message" => "Failed to create delivery. Order has been cancelled.",
                                    "error" => $errorMessage,
                                    "mode" => $doorDashResult['mode'] ?? 'unknown'
                                ], 400);
                            }

                            // Store DoorDash quote data in order
                            OrderMaster::where("code", $order)->update([
                                "doordash_quote_id" => $doorDashResult['data']['external_delivery_id'] ?? null,
                                "doordash_fee" => isset($doorDashResult['data']['fee']) ? $doorDashResult['data']['fee'] / 100 : null,
                                "doordash_response" => $doorDashResult,
                                "doordash_status" => 'QUOTE_CREATED',
                            ]);

                            DoorDashStep::create([
                                'order_id' => $order,
                                'doordash_status' => 'QUOTE_CREATED',
                                'doordash_delivery_id' => $doorDashResult['data']['external_delivery_id'] ?? null,
                                'doordash_response' => json_encode($doorDashResult),
                            ]);
                        }

                        $socketData = [
                            'orderCode' => $order,
                            'orderNumber' =>  $orderCode,
                            'phoneNumber' => $r->mobileNumber,
                            'status' => "pending",
                            'storeCode' => $storeCode,
                            'deliveryType' => $r->deliveryType,
                            "customerName" => $r->customerName,
                            'grandTotal'   => $r->deliveryType === 'delivery'
                                ? $r->grandTotal + (
                                    isset($doorDashResult['data']['fee'])
                                    ? $doorDashResult['data']['fee'] / 100
                                    : 0
                                )
                                : $r->grandTotal,
                            "orderFrom" => "online",
                            "placedBy" => 'client'
                        ];


                        if (
                            $r->deviceType == "mobile"
                        ) {
                            $stripe = new Stripe;

                            $stripeResult = $stripe->makeStripePayment($r->grandTotal);
                            if ($stripeResult) {

                                $result = OrderMaster::where("code", $order)->update(["stripesessionid" => $stripeResult['id']]);

                                return response()->json([
                                    "message" => "Order place successfully.",
                                    "orderCode" => $order,
                                    "receiptNo" => $receiptNo,
                                    "txnId" => $txnId,
                                    "orderDate" => $now,
                                    "totalAmount" => $r->grandTotal,
                                    "stripeResult" => $stripeResult,
                                    "data" => $socketData
                                ], 200);
                            } else {
                                return response()->json(["message" => "Failed to place order."], 400);
                            }
                        } else {

                            // Get payment settings from database
                            $paymentSettings = DB::table('payment_settings')->first();

                            if (!$paymentSettings) {
                                throw new \Exception('Payment settings not found in database');
                            }

                            // Determine mode: 0 = sandbox, 1 = live
                            $mode = $paymentSettings->payment_mode == 1 ? 'LIVE' : 'SANDBOX';

                            // Set secret key based on mode
                            $secretKey = $mode === 'LIVE'
                                ? $paymentSettings->live_secret_key
                                : $paymentSettings->test_secret_key;

                            // Set publishable key for response
                            $publishableKey = $mode === 'LIVE'
                                ? $paymentSettings->live_client_id
                                : $paymentSettings->test_client_id;

                            if (empty($secretKey)) {
                                throw new \Exception("Stripe {$mode} secret key is not configured");
                            }

                            $stripe = new \Stripe\StripeClient($secretKey);

                            $stripeResult = $stripe->checkout->sessions->create([
                                'payment_method_types' => ['card', 'link'],
                                'line_items' => [
                                    [
                                        'price_data' => [
                                            'currency' => 'cad',
                                            'unit_amount' => (int)($r->grandTotal * 100),
                                            'product_data' => [
                                                'name' => 'payment'
                                            ],
                                        ],
                                        'quantity' => 1,
                                    ]
                                ],
                                'metadata' => ["orderId" => $txnId],
                                'mode' => 'payment',
                                'success_url' => env('FRONTEND_URL') . "payment/success",
                                'cancel_url' =>  env("FRONTEND_URL") . "payment/cancel"
                            ]);
                            if ($stripeResult) {

                                $result = OrderMaster::where("code", $order)->update(["stripesessionid" => $stripeResult->id]);

                                return response()->json([
                                    "message" => "Order place successfully.",
                                    "orderCode" => $order,
                                    "receiptNo" => $receiptNo,
                                    "txnId" => $txnId,
                                    "orderDate" => $now,
                                    "totalAmount" => $r->grandTotal,
                                    "stripeResult" => $stripeResult,
                                    "sessionId" => $stripeResult->id,
                                    "publishableKey" => $publishableKey,
                                    "paymentUrl" => $stripeResult->url,
                                    "data" => $socketData
                                ], 200);
                            } else {
                                return response()->json(["message" => "Failed to place order."], 400);
                            }
                        }
                    }
                    return response()->json(["message" => "Failed to place order."], 400);
                }
                return response()->json(["message" => "Failed to place order."], 400);
            } else {
                return response()->json(["message" => "Store not open at this time for store location or on the specified days.", 'isStoreError' => true], 400);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    // Developer: Shreyas Mahamuni, Working Date: 20-12-2023, 21-12-2023, 28-03-2024
    // This Function Return Boolean values true or false with respect to store wise conditions.
    // for STR_1 then check condition as per datetime and week days, same logic structure for another store locations
    // It Called on OrderPlace.
    function isStoreOpen($storeCode)
    {
        if ($storeCode != "") {

            return true;

            $storeData = Storelocation::where('code', $storeCode)->where('isActive', 1)->where('isDelete', 0)->first();
            if ($storeData) {
                $weekdays_start_time = $storeData->weekdays_start_time;
                $weekdays_end_time = $storeData->weekdays_end_time;
                $weekend_start_time  = $storeData->weekend_start_time;
                $weekend_end_time = $storeData->weekend_end_time;

                $todays_date = date('Y-m-d H:i:00');

                $daysOfWeek1 = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
                $daysOfWeek2 = ['Friday', 'Saturday'];

                // daysOfWeek1
                if ($todays_date < date('Y-m-d ' . $weekdays_end_time)) {
                    $startDateTime = date('Y-m-d ' . $weekdays_start_time, strtotime("- 1 day"));
                    $endDateTime = date('Y-m-d ' . $weekdays_end_time);
                } else {
                    $startDateTime = date('Y-m-d ' . $weekdays_start_time);
                    $endDateTime = date('Y-m-d ' . $weekdays_end_time, strtotime("+ 1 day"));
                }
                $startDay = date('l', strtotime($startDateTime));

                if ($todays_date >= $startDateTime && $todays_date <= $endDateTime && in_array($startDay, $daysOfWeek1)) {
                    return true;
                }

                // Check for daysOfWeek2
                if ($todays_date < date('Y-m-d ' . $weekend_end_time)) {
                    $startDateTime = date('Y-m-d ' . $weekend_start_time, strtotime("- 1 day"));
                    $endDateTime = date('Y-m-d ' . $weekend_end_time);
                } else {
                    $startDateTime = date('Y-m-d ' . $weekend_start_time);
                    $endDateTime = date('Y-m-d ' . $weekend_end_time, strtotime("+ 1 day"));
                }

                $startDay = date('l', strtotime($startDateTime));

                if ($todays_date >= $startDateTime && $todays_date <= $endDateTime && in_array($startDay, $daysOfWeek2)) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    public function order_place_with_paymentgateway(Request $r)
    {
        try {
            $currentdate = Carbon::now();
            $now =  $currentdate->toDateTimeString();
            $date =  $currentdate->toDate();
            $input = $r->all();
            $rules = [
                'customerCode'           => 'nullable',
                'mobileNumber'           => ['required', 'numeric'],
                //'deliveryType' 			    => 'required|delivery',
                'products'             => 'required|array|min:1',
                'products.*.id'             => 'required',
                'products.*.productCode'    => 'required',
                'products.*.productName'    => 'required',
                'products.*.productType'    => 'required',
                //'products.*.config'         => 'required',
                'products.*.quantity'       => 'required',
                'products.*.price'          => 'required',
                'products.*.amount'         => 'required',
                'subTotal'             => 'required',
                'discountAmount'         => 'nullable',
                'taxPer'             => 'nullable',
                'taxAmount'           => 'nullable',
                'deliveryCharges'         => 'nullable',
                'extraDeliveryCharges'       => 'nullable',
                'grandTotal'           => 'required',
                'address'                   => 'required|min:10|max:400',
                'zipCode'                   => 'required|regex:/^[ABCEGHJKLMNPRSTVXY]\d[A-Z]\d[A-Z]\d$/i',
                //'deliveryExecutive'       => 'required',
                'customerName'              => 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/',
                'callbackUrl'               => 'required',
                'cancelUrl'                 => 'required'
            ];

            $messages = [
                //'customerCode.required' 			=> 'Customer is required',
                'mobileNumber.required'         => 'Phone number is required',
                'mobileNumber.numeric'           => 'Phone number is invalid',
                //'deliveryType.required' 		    => 'Delivery Type is required',
                //'deliveryType.in' 			        => 'Delivery Type must be delivery',
                'products.required'             => 'Cart is empty, cannot place the order',
                'products.array'             => 'Cart is Invalid',
                'products.min'                 => 'Cart must have at-least one product/item',
                'subTotal.required'           => 'Subtotal is missing',
                'grandTotal.required'           => 'Grand total is missing',
                'products.*.id.required'            => 'Item/Product Id is missing',
                'products.*.productCode.required'   => 'Item/Product Product Code is missing',
                'products.*.productName.required'   => 'Item/Product Product Name is missing',
                'products.*.productType.required'   => 'Item/Product Product Type is missing',
                //'products.*.config.required'        => 'Item/Product Configuration is missing',
                'products.*.quantity.required'      => 'Item/Product Qunatity is missing',
                'products.*.price.required'         => 'Item/Product Price is missing',
                'products.*.amount.required'        => 'Item/Product Amount is missing',
                'address.required'                  => 'Address is required',
                'address.min'                       => "Incomplete address",
                'address.max'                       => "Maximum limit for address is reached",
                'zipCode.required'                  => "Postal Code is required",
                'zipCode.regex'                     => "Enter valid Postal Code.",
                'deliveryExecutive.required'        => "Delivery Executive is required",
                'customerName.required'             => "Customer name is required",
                'customerName.min'                  => "Minimum 3 characters are required for customer name",
                'customerName.max'                  => "Maximum limit reached for customer name",
                'customerName.regex'                => "Invalid customer name",
                'callbackUrl.required'              => 'Callback Url is required',
                'cancelUrl.required'                => 'Cancel Url is required'
            ];

            $validator = Validator::make($input, $rules, $messages);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            if ($r->customerCode != "") {
                $customerCode = $r->customerCode;
            } else {
                $customer = Customer::where('mobileNumber', $r->mobileNumber)->first();
                if ($customer) {
                    $customerCode = $customer->code;
                } else {
                    $customerData = [
                        "fullName" => $r->customerName,
                        "mobileNumber" => $r->mobileNumber,
                        "isActive" => 1,
                        "isDelete" => 0,
                        "created_at" => $currentdate->toDateTimeString(),
                    ];
                    $customer = $this->model->addNew($customerData, "customer", 'CST');
                    if ($customer) {
                        $customerCode = $customer;
                    } else {
                        return response()->json(["message" => "Failed to place order while creating customer's data."], 400);
                    }
                }
            }

            $storeCode = 'STR_1';
            $zipCodeEntry = DB::table('zipcode')->where('zipcode', $r->zipcode)->first();
            if ($zipCodeEntry) {
                $storeCode = $zipCodeEntry->storeCode;
            }

            $delvCode = "";

            $defaultDeliveryExecutive = DB::table('usermaster')
                ->where('role', 'R_2')
                ->where('isActive', 1)
                ->where('defaultDeliveryExecutive', 1)
                ->where('storeLocationCode', $storeCode)
                ->first();
            if ($defaultDeliveryExecutive) {
                $delvCode = $defaultDeliveryExecutive->code;
            } else {
                $defaultDeliveryExecutive = DB::table('usermaster')
                    ->where('role', 'R_2')
                    ->where('isActive', 1)
                    ->where('storeLocationCode', $storeCode)
                    ->first();
                if ($defaultDeliveryExecutive) {
                    $delvCode = $defaultDeliveryExecutive->code;
                }
            }

            $data = [
                "customerCode" => $customerCode,
                "customerName" => $r->customerName,
                "mobileNumber" => $r->mobileNumber,
                "address" => $r->address,
                "deliveryType" => 'delivery',
                "created_at" => $now,
                "orderDate" => $now,
                "addID" => $customerCode,
                "clientType" => 'customer',
                "subTotal" => $r->subTotal,
                "discountAmount" => $r->discountAmount,
                "discountPer" => $r->discountPer,
                "taxAmount" => $r->taxAmount,
                "taxPer" => $r->taxPer,
                "grandTotal" => $r->grandTotal,
                "deliveryCharges" => $r->deliveryCharges,
                "deliveryExecutiveCode" => $delvCode,
                "extraDeliveryCharges" => $r->extraDeliveryCharges,
                "transactionDate" => $now,
                "orderStatus" => "pending",
                "paymentStatus" => "pending",
                "orderFrom" => "online",
                "zipCode" => $r->zipCode,
                "storeLocation" => $storeCode
            ];
            $getOrderCode = OrderMaster::whereDate("created_at", $date)->count();
            if ($getOrderCode == 0) {
                $orderCode = 1;
            } else {
                $orderCode = $getOrderCode + 1;
            }
            $order = $this->model->addNew($data, "ordermaster", 'ORD');
            if ($order) {
                $str = explode('_', $order);
                $id = $str[1];
                $txnId = "SPO" . date('Ymd') . $id;
                $receiptNo = date('Ymd') . $id;
                $result = OrderMaster::where("code", $order)->update(["txnId" => $txnId, "orderCode" => $orderCode]);
                if ($result == true) {
                    foreach ($r->products as $item) {
                        // $orderLineEntries = [
                        // 	"pid" => $item["id"],
                        // 	"orderCode" => $order,
                        // 	"productCode" => $item["productCode"],
                        // 	"productName" => $item["productName"],
                        // 	"productType" => $item["productType"],
                        // 	"config" => json_encode($item["config"]),
                        // 	"quantity" => $item["quantity"],
                        // 	"price" => $item["price"],
                        // 	"amount" => $item["amount"],
                        // 	"pizzaSize" => $item["pizzaSize"],
                        // 	"comments" => $item["comments"] ?? "",
                        // 	"created_at" => $now,
                        // 	"pizzaPrice" => $item['pizzaPrice'] ?? "",
                        // ];
                        // $this->model->addNew($orderLineEntries, "orderlineentries", "ORDL");

                        $orderLine = new OrderLineEntries;
                        $orderLine->code = Str::uuid();
                        $orderLine->pid = $item["id"];
                        $orderLine->orderCode = $order;
                        $orderLine->productCode = $item["productCode"];
                        $orderLine->productName = $item["productName"];
                        $orderLine->productType = $item["productType"];
                        $orderLine->config = json_encode($item["config"]);
                        $orderLine->quantity = $item["quantity"];
                        $orderLine->price = $item["price"];
                        $orderLine->amount = $item["amount"];
                        $orderLine->pizzaSize = $item["pizzaSize"];
                        $orderLine->comments = $item["comments"] ?? "";
                        $orderLine->created_at = $now;
                        $orderLine->pizzaPrice = $item['pizzaPrice'] ?? "0.00";
                        $orderLine->save();
                    }

                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
                    $checkout_session = $stripe->checkout->sessions->create([
                        'payment_method_types' => ['card'],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' => 'cad',
                                    'unit_amount' => $r->grandTotal * 100,
                                    'product_data' => [
                                        'name' => 'payment'
                                    ],
                                ],
                                'quantity' => 1,
                            ]
                        ],
                        'metadata' => ["orderId" => $txnId],
                        'mode' => 'payment',
                        'success_url' => url("customer/payment/success"),
                        'cancel_url' => url("customer/payment/cancel")
                    ]);
                    if ($checkout_session) {
                        OrderMaster::where("code", $order)->update(["stripesessionid" => $checkout_session->id]);
                        return response()->json([
                            "message" => "Order place successfully.",
                            "orderCode" => $order,
                            "sessionId" => $checkout_session->id,
                            "paymentUrl" => $checkout_session->url
                        ], 200);
                    } else {
                        return response()->json(["message" => "Failed to place order."], 400);
                    }
                }
                return response()->json(["message" => "Failed to place order."], 400);
            }
            return response()->json(["message" => "Failed to place order."], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function get_order_list(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'customerCode' => 'required',
                'orderStatus' => 'nullable',
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $orderQuery = OrderMaster::join("customer as u1", "u1.code", "=", "ordermaster.addID", "left")
                ->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
                ->select("ordermaster.*", "u1.fullName as customerName", "u2.username as deliveryExecutiveName")
                ->whereBetween('ordermaster.created_at', [date('Y-m-d 00:00:00', strtotime("- 5 days")), date('Y-m-d 23:59:59')])
                ->where("ordermaster.customerCode", $r->customerCode);

            if ($r->has('orderStatus') && $r->orderStatus != "") {
                $orderQuery->where("ordermaster.orderStatus", $r->orderStatus);
            } else {
                $orderQuery->whereNotIn("ordermaster.orderStatus", ["delivered", "cancelled"]);
            }

            $getOrder = $orderQuery->orderBy('ordermaster.id', 'DESC')->get();
            if ($getOrder && count($getOrder) > 0) {
                $orderArray = [];
                foreach ($getOrder as $item) {
                    $data = [
                        "code" => $item->code,
                        "orderFrom" => $item->orderFrom,
                        "orderCode" => $item->orderCode,
                        "receiptNo" => $item->receiptNo,
                        "customerCode" => $item->customerCode,
                        "customerName" => $item->customerName,
                        "mobileNumber" => $item->mobileNumber,
                        "address" => $item->address ?? "",
                        "deliveryType" => $item->deliveryType ?? "",
                        "customerName" => $item->customerName ?? "",
                        "deliveryExecutiveCode" => $item->deliveryExecutiveCode ?? "",
                        "deliveryExecutiveName" => $item->deliveryExecutiveName ?? "",
                        "comments" => $item->comments ?? "",
                        "created_at" => date('d-m-Y h:i A', strtotime($item->created_at)) ?? "",
                        "orderDate" =>  date('d-m-Y h:i A', strtotime($item->orderDate)) ?? "",
                        "clientType" => $item->clientType ?? "",
                        "subTotal" => $item->subTotal ?? "0.00",
                        "discountPer" => $item->discountPer ?? "0.00",
                        "discountmount" => $item->discountAmount ?? "0.00",
                        "taxPer" => $item->taxPer ?? "0.00",
                        "taxAmount" => $item->taxAmount ?? "0.00",
                        "grandTotal" => $item->grandTotal ?? "0.00",
                        "orderStatus" => $item->orderStatus ?? "",
                        "zipCode" => $item->zipCode ?? "",
                    ];

                    array_push($orderArray, $data);
                }
                return response()->json(["message" => "Data found", "data" => $orderArray], 200);
            }
            return response()->json(["message" => "Orders not found."], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function customer_order_list(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'customerCode' => 'nullable',
                'transactionId' => 'nullable',
                'fromDate' => 'nullable',
                'toDate' => 'nullable',
                'page' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $fromdate = date('Y-m-d 00:00:00');
            $todate = date('Y-m-d 23:59:59');

            if ($r->fromDate != "" && $r->toDate != "") {
                $fromDate = date('Y-m-d', strtotime($r->fromDate));
                $toDate = date('Y-m-d', strtotime($r->toDate));
                if ($toDate > $fromDate) {
                    $fromdate = $fromDate . " 00:00:00";
                    $todate = $toDate . " 23:59:59";
                }
            }

            $getMobileNumber = Customer::where('code', $r->customerCode)->first();


            // Backup 18-04-2024
            // $getCount = OrderMaster::join("customer as u1", "u1.code", "=", "ordermaster.addID", "left")
            // 	->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
            // 	->select("ordermaster.*", "u1.fullName as customerName", "u2.username as deliveryExecutiveName")
            // 	->where("ordermaster.customerCode", $r->customerCode);


            $getCount = OrderMaster::join("customer as u1", "u1.code", "=", "ordermaster.addID", "left")
                ->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
                ->select("ordermaster.*", "u1.fullName as customerName", "u2.username as deliveryExecutiveName")
                ->join('customer', 'customer.mobileNumber', '=', 'ordermaster.mobileNumber')
                ->where('ordermaster.mobileNumber', $getMobileNumber->mobileNumber);

            if ($r->fromDate != "" && $r->toDate != "") {
                $getCount->whereBetween('ordermaster.created_at', [$fromdate, $todate]);
            }
            if ($r->has('transactionId') && $r->transactionId != "") {
                $getCount->where('ordermaster.txnId', $r->transactionId);
            }
            $count = $getCount->groupBy("ordermaster.id")->orderBy('ordermaster.id', 'DESC')->count();
            $perpage = 10;
            $page = 1;
            if ($r->page != "") {
                $page = $r->page;
            }
            $totalPages = ceil($count / $perpage);
            if ($page > $totalPages) {
                $page = $totalPages;
            }
            $offset = ($page - 1) * $perpage;

            // Backup - 18-04-2024
            // $orderQuery = OrderMaster::join("customer as u1", "u1.code", "=", "ordermaster.addID", "left")
            // 	->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
            // 	->select("ordermaster.*", "u1.fullName as customerName", "u2.username as deliveryExecutiveName")
            // 	->where("ordermaster.customerCode", $r->customerCode);



            $orderQuery = OrderMaster::join("customer as u1", "u1.code", "=", "ordermaster.addID", "left")
                ->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
                ->select("ordermaster.*", "u1.fullName as customerName", "u2.username as deliveryExecutiveName")
                ->join('customer', 'customer.mobileNumber', '=', 'ordermaster.mobileNumber')
                ->where('ordermaster.mobileNumber', $getMobileNumber->mobileNumber);


            if ($r->fromDate != "" && $r->toDate != "") {
                $orderQuery->whereBetween('ordermaster.created_at', [$fromdate, $todate]);
            }
            if ($r->has('transactionId') && $r->transactionId != "") {
                $orderQuery->where('ordermaster.txnId', $r->transactionId);
            }
            $getOrder = $orderQuery->groupBy("ordermaster.id")->orderBy('ordermaster.id', 'DESC')
                ->skip($offset)
                ->limit($perpage)
                ->get();

            if ($getOrder && count($getOrder) > 0) {
                $orderArray = [];
                foreach ($getOrder as $item) {
                    $orderItem = "";
                    $getOrderItem = OrderLineEntries::select("orderlineentries.*")
                        ->where("orderlineentries.orderCode", $item->code)
                        ->first();
                    if (!empty($getOrderItem)) {
                        $image = "";
                        $productType = $getOrderItem->productType;
                        $productCode = $getOrderItem->productCode;

                        $imagePaths = [
                            'special_pizza'   => ['model' => Specialoffer::class, 'field' => 'specialofferphoto', 'path' => 'uploads/specialoffer/', 'default' => 'public/uploads/pizza.jpg'],
                            'signature_pizza' => ['model' => SignaturePizza::class, 'field' => 'pizza_image', 'path' => 'uploads/signature-pizza/', 'default' => 'public/uploads/pizza.jpg'],
                            'other_pizza'     => ['model' => Pizzas::class, 'field' => 'pizza_image', 'path' => 'uploads/pizzas/', 'default' => 'public/uploads/pizza.jpg'],
                            'side'            => ['model' => SidesMaster::class, 'field' => 'image', 'path' => 'uploads/sides/', 'default' => 'public/uploads/sample_sides.png'],
                            'dips'            => ['model' => Dips::class, 'field' => 'dipsImage', 'path' => 'uploads/dips/', 'default' => null],
                            'drinks'          => ['model' => Softdrinks::class, 'field' => 'softDrinkImage', 'path' => 'uploads/softdrinks/', 'default' => null],
                        ];

                        if (array_key_exists($productType, $imagePaths)) {
                            $config = $imagePaths[$productType];
                            $product = $config['model']::where('code', $productCode)->first();

                            if ($product && !empty($product->{$config['field']})) {
                                $image = url($config['path'] . $product->{$config['field']});
                            } else {
                                $image = isset($config['default']) ? url($config['default']) : '';
                            }
                        }

                        $orderItem = [
                            "productCode" => $getOrderItem->productCode,
                            "productType" => $getOrderItem->productType,
                            "productName" => $getOrderItem->productName,
                            "quantity" => $getOrderItem->quantity ?? "",
                            "price" => $getOrderItem->price ?? "",
                            'image' => $image,
                            "amount" => $getOrderItem->amount ?? "",
                            "pizzaSize" => $getOrderItem->pizzaSize ?? "",
                            "pizzaPrice" => $getOrderItem->pizzaPrice ?? "0.00"
                        ];
                    }

                    $data = [
                        "code" => $item->code,
                        "orderFrom" => $item->orderFrom,
                        "orderCode" => $item->orderCode,
                        "customerCode" => $item->customerCode,
                        "customerName" => $item->customerName,
                        "txnId" => $item->txnId,
                        "mobileNumber" => $item->mobileNumber,
                        "address" => $item->address ?? "",
                        "storeLocation" => $item->storeLocation ?? "",
                        "deliveryType" => $item->deliveryType ?? "",
                        "storeLocationCode" => $item->storeLocationCode ?? "",
                        "cashierCode" => $item->addID ?? "",
                        "cashierName" => $item->CashierName ?? "",
                        "deliveryExecutiveCode" => $item->deliveryExecutiveCode ?? "",
                        "deliveryExecutiveName" => $item->deliveryExecutiveName ?? "",
                        "comments" => $item->comments ?? "",
                        "created_at" => date('d-m-Y h:i A', strtotime($item->created_at)) ?? "",
                        "orderDate" =>  date('d-m-Y h:i A', strtotime($item->orderDate)) ?? "",
                        "clientType" => $item->clientType ?? "",
                        "subTotal" => $item->subTotal ?? "0.00",
                        "discountPer" => $item->discountPer ?? "0.00",
                        "discountmount" => $item->discountAmount ?? "0.00",
                        "taxPer" => $item->taxPer ?? "0.00",
                        "taxAmount" => $item->taxAmount ?? "0.00",
                        "grandTotal" => $item->grandTotal ?? "0.00",
                        "orderStatus" => $item->orderStatus,
                        "orderItem" => $orderItem,
                    ];
                    array_push($orderArray, $data);
                }
                return response()->json(["message" => "Data found", "currentPage" => $page, "perPage" => $perpage, "totalPages" => $totalPages, "totalCount" => $count, "data" => $orderArray], 200);
            }
            return response()->json(["message" => "Data not found.", $getOrder], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function get_order_details(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'orderCode' => 'required',
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $getOrder =  OrderMaster::join("customer as u1", "u1.code", "=", "ordermaster.addID", "left")
                ->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
                ->select("ordermaster.*", "u2.username as deliveryExecutiveName")
                ->where("ordermaster.code", $r->orderCode)
                ->first();

            $orderItems = [];
            if (!empty($getOrder)) {
                $getOrderItem = OrderLineEntries::select("orderlineentries.*")
                    ->where("orderlineentries.orderCode", $r->orderCode)
                    ->get();
                if ($getOrderItem && count($getOrderItem) > 0) {
                    foreach ($getOrderItem as $item) {
                        $image = "";
                        $productType = $item->productType;
                        $productCode = $item->productCode;

                        $imagePaths = [
                            'special_pizza'   => ['model' => Specialoffer::class, 'field' => 'specialofferphoto', 'path' => 'uploads/specialoffer/', 'default' => 'public/uploads/pizza.jpg'],
                            'signature_pizza' => ['model' => SignaturePizza::class, 'field' => 'pizza_image', 'path' => 'uploads/signature-pizza/', 'default' => 'public/uploads/pizza.jpg'],
                            'other_pizza'     => ['model' => Pizzas::class, 'field' => 'pizza_image', 'path' => 'uploads/pizzas/', 'default' => 'public/uploads/pizza.jpg'],
                            'side'            => ['model' => SidesMaster::class, 'field' => 'image', 'path' => 'uploads/sides/', 'default' => 'public/uploads/sample_sides.png'],
                            'dips'            => ['model' => Dips::class, 'field' => 'dipsImage', 'path' => 'uploads/dips/', 'default' => null],
                            'drinks'          => ['model' => Softdrinks::class, 'field' => 'softDrinkImage', 'path' => 'uploads/softdrinks/', 'default' => null],
                        ];

                        if (array_key_exists($productType, $imagePaths)) {
                            $config = $imagePaths[$productType];
                            $product = $config['model']::where('code', $productCode)->first();

                            if ($product && !empty($product->{$config['field']})) {
                                $image = url($config['path'] . $product->{$config['field']});
                            } else {
                                $image = isset($config['default']) ? url($config['default']) : '';
                            }
                        }
                        $orderitem = [
                            "id" => $item->pid,
                            "code" => $item->code,
                            "productCode" => $item->productCode,
                            "productType" => $item->productType,
                            "productName" => $item->productName,
                            "config" => json_decode($item->config, true),
                            "quantity" => $item->quantity ?? "",
                            "price" => $item->price ?? "",
                            "amount" => $item->amount ?? "",
                            'image' => $image,
                            "pizzaSize" => $item->pizzaSize ?? "",
                            "comments" => $item->comments ?? "",
                            "pizzaPrice" => $item->pizzaPrice ?? "0.00"
                        ];
                        array_push($orderItems, $orderitem);
                    }
                }

                $defaultAddress = '2120 N Park Unit #25, Brampton, ON L6S0CP';
                $store = DB::table("storelocation")->where('code', $getOrder->storeLocation)->first();
                if ($store) {
                    $storeLocation = $store->storeLocation;
                    $storeAddress =  $store->storeAddress != "" ? $store->storeAddress : $defaultAddress;
                } else {
                    $storeLocation = "North Park / Torbram";
                    $storeAddress = $defaultAddress;
                }

                $data["code"] = $getOrder->code;
                //$data["id"]=$getOrder->pid??"";
                $data["orderCode"] = $getOrder->orderCode ?? "";
                $data["customerCode"] = $getOrder->customerCode ?? "";
                $data["customerName"] = $getOrder->customerName ?? "";
                $data["mobileNumber"] = $getOrder->mobileNumber ?? "";
                $data["orderFrom"] = $getOrder->orderFrom ?? "store";
                $data["address"] = $getOrder->address ?? "";
                $data["deliveryType"] = $getOrder->deliveryType ?? "";
                $data["deliveryExecutiveCode"] = $getOrder->deliveryExecutiveCode ?? "";
                $data["deliveryExecutiveName"] = $getOrder->deliveryExecutiveName ?? "";
                $data["comments"] = $getOrder->comments ?? "";
                $data["created_at"] =  date('d-m-Y h:i A', strtotime($getOrder->created_at)) ?? "";
                $data["orderDate"] =  date('d-m-Y h:i A', strtotime($getOrder->orderDate)) ?? "";
                $data["clientType"] = $getOrder->clientType ?? "";
                $data["subTotal"] = $getOrder->subTotal ?? "0.00";
                $data["discountmount"] = $getOrder->discountAmount ?? "0.00";
                $data["discountPer"] = $getOrder->discountPer ?? "0.00";
                $data["taxAmount"] = $getOrder->taxAmount ?? "0.00";
                $data["taxPer"] = $getOrder->taxPer ?? "0.00";
                $data["deliveryCharges"] = $getOrder->deliveryCharges ?? "0.00";
                $data["extraDeliveryCharges"] = $getOrder->extraDeliveryCharges ?? "0.00";
                $data["grandTotal"] = $getOrder->grandTotal ?? "0.00";
                $data["orderStatus"] = $getOrder->orderStatus;
                $data["orderItems"] = $orderItems;
                $data["zipCode"] = $getOrder->zipCode ?? "";
                $data["storeAddress"] = $storeAddress;
                $data["storeLocation"] = $storeLocation;

                return response()->json(["message" => "Data found", "data" => $data], 200);
            }
            return response()->json(["message" => "Order does not exist"], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function webhook_old(Request $r)
    {
        try {
            $endpoint_secret = env('WEBHOOK_SECRET_KEY');

            $webhookResponse = $r->all();

            Log::info("payment web hook " . json_encode($webhookResponse));

            $paymentObject = $webhookResponse->data->object;
            $stripeSessionId = $paymentObject->id;

            $payment_status = $paymentObject->paymentStatus;
            $stripePaymentStatus = $event->data->object->status;

            $result = DB::table("ordermaster")->select("ordermaster.*")->where("stripesessionid", $stripeSessionId)->first();
            if (!empty($result)) {
                if ($result->orderStatus === 'pending') {
                    if ($payment_status == "paid") {
                        $data['orderStatus'] = 'placed';
                        $data['paymentStatus'] = "paid";
                        $data['webHookResponse'] = json_encode($webhookResponse);
                        $data['paymentOrderId'] = $paymentObject->payment_intent;
                        $this->model->doEdit($data, 'ordermaster', $result->code);
                    } elseif ($payment_status == "canceled") {

                        $data['orderStatus'] = 'canceled';
                        $data['paymentStatus'] = "paid";
                        $data['webHookResponse'] = json_encode($webhookResponse);
                        $data['paymentOrderId'] = $paymentObject->payment_intent;
                        $this->model->doEdit($data, 'ordermaster', $result->code);
                    }
                }
            }
            return response()->json(["status" => 200, "message" => "Payment Successfull."]);
        } catch (\Exception $ex) {
            return response()->json(["status" => 200, "message" => "Payment Successfull."]);
        }
    }
}
