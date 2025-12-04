<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Classes\FirebaseNotification;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\ApiModel;
use App\Models\Users;
use App\Models\Customer;
use App\Models\Zipcode;
use App\Models\CashierCartMaster;
use App\Models\CashierCartLineEntries;
use App\Models\OrderMaster;
use App\Models\OrderLineEntries;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class CashierOrderController extends Controller
{
    public function __construct(GlobalModel $model, ApiModel $apimodel)
    {
        $this->model = $model;
        $this->apimodel = $apimodel;
    }

    public function order_place(Request $r)
    {
        try {

            $storeLocation = $r->storeLocation;
            $store = DB::table('storelocation')
                ->select("storelocation.timezone")
                ->where('code', $storeLocation)
                ->first();

            if (!empty($store)) {
                $timezone = $store->timezone;
                Carbon::now($timezone);
                date_default_timezone_set($timezone);
            }

            $currentdate = Carbon::now();
            $now =  $currentdate->toDateTimeString();

            $input = $r->all();

            $rules = [
                'cashierCode'                 => 'required',
                'mobileNumber'                 => ['required', 'numeric'],
                'customerEmail'                => 'nullable|email',
                'deliveryType'                 => 'required|in:pickup,delivery',
                'storeLocation'             => 'required',
                'products'                     => 'required|array|min:1',
                'products.*.id'             => 'required',
                'products.*.productCode'    => 'required',
                'products.*.productName'    => 'required',
                'products.*.productType'    => 'required',
                //'products.*.config'         => 'required',
                'products.*.quantity'       => 'required',
                'products.*.price'          => 'required',
                'products.*.amount'         => 'required',
                'subTotal'                     => 'required',
                'discountAmount'             => 'nullable',
                'taxPer'                     => 'nullable',
                'taxAmount'                 => 'nullable',
                'deliveryCharges'             => 'nullable',
                'extraDeliveryCharges'         => 'nullable',
                'grandTotal'                 => 'required',
                'deliveryExecutive'         => 'nullable',
                'orderTakenBy'                => 'nullable',
            ];

            $messages = [
                'cashierCode.required'                 => 'Cashier is missing or not logged in',
                'mobileNumber.required'             => 'Phone number is required',
                'mobileNumber.numeric'                 => 'Phone number is invalid',
                'customerEmail.email'              => 'Email is invalid',
                'deliveryType.required'             => 'Delivery Type is required',
                'deliveryType.in'                     => 'Delivery Type must be pickup or delivery',
                'storeLocation.required'             => 'Store location is required',
                'products.required'                 => 'Cart is empty, cannot place the order',
                'products.array'                     => 'Cart is Invalid',
                'products.min'                         => 'Cart must have at-least one product/item',
                'subTotal.required'                 => 'Subtotal is missing',
                'grandTotal.required'                 => 'Grand total is missing',
                'products.*.id.required'            => 'Item/Product Id is missing',
                'products.*.productCode.required'   => 'Item/Product Product Code is missing',
                'products.*.productName.required'   => 'Item/Product Product Name is missing',
                'products.*.productType.required'   => 'Item/Product Product Type is missing',
                //'products.*.config.required'        => 'Item/Product Configuration is missing',
                'products.*.quantity.required'      => 'Item/Product Qunatity is missing',
                'products.*.price.required'         => 'Item/Product Price is missing',
                'products.*.amount.required'        => 'Item/Product Amount is missing',
            ];

            if ($r->deliveryType != "pickup") {
                $rules['address'] = 'required|min:10|max:400';
                $rules['zipCode'] = 'required|regex:/^[ABCEGHJKLMNPRSTVXY]\d[A-Z]\d[A-Z]\d$/i';
                //$rules['deliveryExecutive'] = 'required';
                $rules['customerName'] = 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/';

                $messages['address.required'] = "Address is required";
                $messages['address.min'] = "Incomplete address";
                $messages['address.max'] = "Maximum limit for address is reached";
                $messages['zipCode.required'] = "Postal Code is required";
                $messages['zipCode.regex'] = "Enter Valid Postal Code.";
                //$messages['deliveryExecutive.required'] = "Delivery Executive is required";
                $messages['customerName.required'] = "Customer name is required";
                $messages['customerName.min'] = "Minimum 3 characters are required for customer name";
                $messages['customerName.max'] = "Maximum limit reached for customer name";
                $messages['customerName.regex'] = "Invalid customer name";
            } else {
                $rules['customerName'] = 'nullable|min:3|max:100|regex:/^[a-zA-Z\s]+$/';

                $messages['customerName.min'] = "Minimum 3 characters are required for customer name";
                $messages['customerName.max'] = "Maximum limit reached for customer name";
                $messages['customerName.regex'] = "Invalid customer name";
            }

            $validator = Validator::make($input, $rules, $messages);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $customerCode = "";
            $customer = Customer::where('mobileNumber', $r->mobileNumber)->first();
            if ($customer) {
                $customerCode = $customer->code;
            }
            // } else {
            //     $customerData = [
            //         "fullName" => $r->customerName,
            //         "mobileNumber" => $r->mobileNumber,
            //         "isActive" => 1,
            //         "isDelete" => 0,
            //         "created_at" => $now,
            //     ];
            //     $customer = $this->model->addNew($customerData, "customer", 'CST');
            //     if ($customer) {
            //         $customerCode = $customer;
            //     } else {
            //         return response()->json(["message" => "Failed to place order while creating customer's data."], 400);
            //     }
            // }

            $deliveryExecutive = "";
            if ($r->deliveryType != "pickup") {
                if ($r->has('deliveryExecutive') && $r->deliveryExecutive != "") {
                    $deliveryExecutive = $r->deliveryExecutive;
                } else {
                    $delivery = DB::table('usermaster')->where('defaultDeliveryExecutive', 1)->first();
                    if ($delivery) {
                        $deliveryExecutive = $delivery->code;
                    }
                }
            }

            $data = [
                "customerCode" => $customerCode,
                "customerName" => $r->customerName,
                "customerEmail" => $r->customerEmail,
                "mobileNumber" => $r->mobileNumber,
                "address" => $r->address,
                "deliveryType" => $r->deliveryType,
                "storeLocation" => $r->storeLocation,
                "created_at" => $now,
                "orderDate" => $now,
                "addID" => $r->cashierCode,
                "clientType" => 'cashier',
                "subTotal" => $r->subTotal,
                "discountAmount" => $r->discountAmount,
                "discountPer" => $r->discountPer,
                "taxAmount" => $r->taxAmount,
                "taxPer" => $r->taxPer,
                "grandTotal" => $r->grandTotal,
                "deliveryCharges" => $r->deliveryCharges,
                "deliveryExecutiveCode" => $deliveryExecutive,
                "extraDeliveryCharges" => $r->extraDeliveryCharges,
                "transactionDate" => $now,
                "paymentStatus" => "paid",
                "orderStatus" => "placed",
                "orderFrom" => "store",
                "zipCode" => $r->zipCode,
                "orderTakenBy" => $r->orderTakenBy,
                //"transactionResponse"=>"",
            ];

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

            $order = $this->model->addNew($data, "ordermaster", 'ORD');
            if ($order) {
                $str = explode('_', $order);
                $id = $str[1];
                $txnId = "SPO" . date('Ymd') . $id;
                $result = OrderMaster::where("code", $order)->update(["txnId" => $txnId, "orderCode" => $orderCode]);
                if ($result == true) {
                    foreach ($r->products as $item) {
                        // $orderLineEntries = [
                        //   "pid" => $item["id"],
                        //   "orderCode" => $order,
                        //   "productCode" => $item["productCode"],
                        //   "productName" => $item["productName"],
                        //   "productType" => $item["productType"],
                        //   "config" => json_encode($item["config"]),
                        //   "quantity" => $item["quantity"],
                        //   "price" => $item["price"],
                        //   "amount" => $item["amount"],
                        //   "pizzaSize" => $item["pizzaSize"],
                        //   "comments" => $item["comments"] ?? "",
                        //   "created_at" => $now,
                        //   "pizzaPrice" => $item['pizzaPrice'] ?? "0.00"
                        // ];
                        //$this->model->addNew($orderLineEntries, "orderlineentries", "ORDL");

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
                    // $uri .= '/order/place/cashier';
                    // $response = Http::get($uri, [
                    //   'orderCode' => $order,
                    //   'orderNumber' =>  $orderCode,
                    //   'phoneNumber' => $r->mobileNumber,
                    //   'status' => "placed",
                    //   'storeCode' => $r->storeLocation,
                    //   'deliveryType' => $r->deliveryType,
                    //   "customerName" => $r->customerName,
                    //   "grandTotal" => $r->grandTotal,
                    //   "orderFrom" => "store"
                    // ]);

                    $socketData = [
                        'orderCode' => $order,
                        'orderNumber' =>  $orderCode,
                        'phoneNumber' => $r->mobileNumber,
                        'status' => "placed",
                        'storeCode' => $r->storeLocation,
                        'deliveryType' => $r->deliveryType,
                        "customerName" => $r->customerName,
                        "grandTotal" => $r->grandTotal,
                        "orderFrom" => "store",
                        "placedBy" => 'cashier'
                    ];

                    return response()->json(["message" => "Order placed successfully.", "orderCode" => $order, "code" => $orderCode, "data" => $socketData], 200);
                }
                return response()->json(["message" => "Failed to place order."], 400);
            }
            return response()->json(["message" => "Failed to place order."], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function order_edit(Request $r)
    {
        try {

            $storeLocation = $r->storeLocation;
            $store = DB::table('storelocation')
                ->select("storelocation.timezone")
                ->where('code', $storeLocation)
                ->first();

            if (!empty($store)) {
                $timezone = $store->timezone;
                Carbon::setLocale($timezone);
                date_default_timezone_set($timezone);
            }

            $currentdate = Carbon::now();
            $now =  $currentdate->toDateTimeString();
            $date =  $currentdate->toDate();
            $input = $r->all();

            $rules = [
                'orderCode'                    => 'required',
                'cashierCode'                 => 'required',
                'mobileNumber'                 => ['required', 'numeric'],
                'customerEmail'                    => 'nullable|email',
                'deliveryType'                 => 'required|in:pickup,delivery',
                'storeLocation'             => 'required',
                'products'                     => 'required|array|min:1',
                'products.*.id'             => 'required',
                'products.*.productCode'    => 'required',
                'products.*.productName'    => 'required',
                'products.*.productType'    => 'required',
                //'products.*.config'         => 'required',
                'products.*.quantity'       => 'required',
                'products.*.price'          => 'required',
                'products.*.amount'         => 'required',
                'subTotal'                     => 'required',
                'discountAmount'             => 'nullable',
                'taxPer'                     => 'nullable',
                'taxAmount'                 => 'nullable',
                'deliveryCharges'             => 'nullable',
                'extraDeliveryCharges'         => 'nullable',
                'grandTotal'                 => 'required',
                'deliveryExecutive'         => 'nullable',
                'orderTakenBy'                => 'nullable',
            ];

            $messages = [
                'orderCode.required'                => 'Order Code is Required',
                'cashierCode.required'                 => 'Cashier is missing or not logged in',
                'mobileNumber.required'             => 'Phone number is required',
                'mobileNumber.numeric'                 => 'Phone number is invalid',
                'customerEmail.email'                => 'Email is invalid',
                'deliveryType.required'             => 'Delivery Type is required',
                'deliveryType.in'                     => 'Delivery Type must be pickup or delivery',
                'storeLocation.required'             => 'Store location is required',
                'products.required'                 => 'Cart is empty, cannot place the order',
                'products.array'                     => 'Cart is Invalid',
                'products.min'                         => 'Cart must have at-least one product/item',
                'subTotal.required'                 => 'Subtotal is missing',
                'grandTotal.required'                 => 'Grand total is missing',
                'products.*.id.required'            => 'Item/Product Id is missing',
                'products.*.productCode.required'   => 'Item/Product Product Code is missing',
                'products.*.productName.required'   => 'Item/Product Product Name is missing',
                'products.*.productType.required'   => 'Item/Product Product Type is missing',
                //'products.*.config.required'        => 'Item/Product Configuration is missing',
                'products.*.quantity.required'      => 'Item/Product Qunatity is missing',
                'products.*.price.required'         => 'Item/Product Price is missing',
                'products.*.amount.required'        => 'Item/Product Amount is missing',
            ];

            if ($r->deliveryType != "pickup") {
                $rules['address'] = 'required|min:10|max:400';
                $rules['zipCode'] = 'required|regex:/^[ABCEGHJKLMNPRSTVXY]\d[A-Z]\d[A-Z]\d$/i';
                //$rules['deliveryExecutive'] = 'required';
                $rules['customerName'] = 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/';

                $messages['address.required'] = "Address is required";
                $messages['address.min'] = "Incomplete address";
                $messages['address.max'] = "Maximum limit for address is reached";
                $messages['zipCode.required'] = "Postal Code is required";
                $messages['zipCode.regex'] = "Enter Valid Postal Code.";
                //$messages['deliveryExecutive.required'] = "Delivery Executive is required";
                $messages['customerName.required'] = "Customer name is required";
                $messages['customerName.min'] = "Minimum 3 characters are required for customer name";
                $messages['customerName.max'] = "Maximum limit reached for customer name";
                $messages['customerName.regex'] = "Invalid customer name";
            } else {
                $rules['customerName'] = 'nullable|min:3|max:100|regex:/^[a-zA-Z\s]+$/';

                $messages['customerName.min'] = "Minimum 3 characters are required for customer name";
                $messages['customerName.max'] = "Maximum limit reached for customer name";
                $messages['customerName.regex'] = "Invalid customer name";
            }

            $validator = Validator::make($input, $rules, $messages);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

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
                $customer = $this->model->doEdit($customerData, "customer", $r->orderCode);
                if (isset($customer)) {
                    $customerCode = $customer;
                } else {
                    return response()->json(["message" => "Failed to place order while updating customer's data."], 400);
                }
            }

            $deliveryExecutive = "";
            if ($r->deliveryType != "pickup") {
                if ($r->has('deliveryExecutive') && $r->deliveryExecutive != "") {
                    $deliveryExecutive = $r->deliveryExecutive;
                } else {
                    $delivery = DB::table('usermaster')->where('defaultDeliveryExecutive', 1)->first();
                    if ($delivery) {
                        $deliveryExecutive = $delivery->code;
                    }
                }
            }

            $data = [
                "customerCode" => $customerCode,
                "customerName" => $r->customerName,
                "customerEmail" => $r->customerEmail,
                "mobileNumber" => $r->mobileNumber,
                "address" => $r->address,
                "deliveryType" => $r->deliveryType,
                "storeLocation" => $r->storeLocation,
                "created_at" => $now,
                "orderDate" => $now,
                "addID" => $r->cashierCode,
                "clientType" => 'cashier',
                "subTotal" => $r->subTotal,
                "discountAmount" => $r->discountAmount,
                "discountPer" => $r->discountPer,
                "taxAmount" => $r->taxAmount,
                "taxPer" => $r->taxPer,
                "grandTotal" => $r->grandTotal,
                "deliveryCharges" => $r->deliveryCharges,
                "deliveryExecutiveCode" => $deliveryExecutive,
                "extraDeliveryCharges" => $r->extraDeliveryCharges,
                "transactionDate" => $now,
                "paymentStatus" => "paid",
                "orderStatus" => "placed",
                "orderFrom" => "store",
                "zipCode" => $r->zipCode,
                "orderTakenBy" => $r->orderTakenBy,
                //"transactionResponse"=>"",
            ];
            $getOrderCode = OrderMaster::whereDate("created_at", $date)->count();
            if ($getOrderCode == 0) {
                $orderCode = 1;
            } else {
                $orderCode = $getOrderCode + 1;
            }
            $order = $this->model->doEdit($data, "ordermaster", $r->orderCode);
            if (isset($order)) {
                DB::table("orderlineentries")->where('orderCode', '=', $r->orderCode)->delete();
                foreach ($r->products as $item) {
                    // $orderLineEntries = [
                    //   "pid" => $item["id"],
                    //   "orderCode" => $r->orderCode,
                    //   "productCode" => $item["productCode"],
                    //   "productName" => $item["productName"],
                    //   "productType" => $item["productType"],
                    //   "config" => json_encode($item["config"]),
                    //   "quantity" => $item["quantity"],
                    //   "price" => $item["price"],
                    //   "amount" => $item["amount"],
                    //   "pizzaSize" => $item["pizzaSize"],
                    //   "comments" => $item["comments"] ?? "",
                    //   "created_at" => $now,
                    //   "pizzaPrice" => $item['pizzaPrice'] ?? "0.00"
                    // ];
                    //$this->model->addNew($orderLineEntries, "orderlineentries", "ORDL");
                    $orderLine = new OrderLineEntries;
                    $orderLine->code = Str::uuid();
                    $orderLine->pid = $item["id"];
                    $orderLine->orderCode = $r->orderCode;
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
                return response()->json(["message" => "Order Updated successfully.", "orderCode" => $r->orderCode], 200);
            }
            return response()->json(["message" => "Failed to place order."], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function delivery_executive_assign(Request $r)
    {
        try {
            $currentdate = Carbon::now();
            $input = $r->all();
            $validator = Validator::make($input, [
                'orderCode' => 'required',
                'deliveryExecutiveCode' => 'required',
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $getOrder = OrderMaster::select("ordermaster.*")
                ->where("ordermaster.code", $r->orderCode)
                ->first();
            if (!empty($getOrder)) {
                $result = OrderMaster::where("code", $r->orderCode)->update(["deliveryExecutiveCode" => $r->deliveryExecutiveCode]);
                if ($result == true) {
                    return response()->json(["message" => "Delivery Executive added successfully."], 200);
                }
                return response()->json(["message" => "Failed to added delivery executive."], 200);
            }
            return response()->json(["message" => "Order does not exist"], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function get_order_list(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'cashierCode' => 'required',
                'orderFrom' => 'required|in:all,store,online',
                'orderStatus' => 'nullable',
                'phoneno' => 'nullable'
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $curDate = date('Y-m-d H:i:00');

            $orderQuery = OrderMaster::join("usermaster as u1", "u1.code", "=", "ordermaster.addID", "left")
                ->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
                ->join("storelocation", "storelocation.code", "=", "ordermaster.storeLocation")
                ->select("ordermaster.*", "u1.username as CashierName", "u2.username as deliveryExecutiveName", "storelocation.storeLocation as storeLocationName");

            /*  
                if ($curDate < date("Y-m-d 06:00:00")) {
                $orderQuery = OrderMaster::join("usermaster as u1", "u1.code", "=", "ordermaster.addID", "left")
                    ->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
                    ->join("storelocation", "storelocation.code", "=", "ordermaster.storeLocation")
                    ->select("ordermaster.*", "u1.username as CashierName", "u2.username as deliveryExecutiveName", "storelocation.storeLocation as storeLocationName")
                    ->whereBetween('ordermaster.created_at', [date('Y-m-d 10:30:00', strtotime("- 1 days")), date("Y-m-d 06:00:00")]);
                } else {
                $orderQuery = OrderMaster::join("usermaster as u1", "u1.code", "=", "ordermaster.addID", "left")
                    ->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
                    ->join("storelocation", "storelocation.code", "=", "ordermaster.storeLocation")
                    ->select("ordermaster.*", "u1.username as CashierName", "u2.username as deliveryExecutiveName", "storelocation.storeLocation as storeLocationName")
                    ->whereBetween('ordermaster.created_at', [date("Y-m-d 10:30:00"), date('Y-m-d 06:00:00', strtotime("+ 1 days"))]);
                }
            */

            //activate when multistore
            //->where("ordermaster.addID", $r->cashierCode);

            if ($r->has('orderFrom') && $r->orderFrom != "") {
                if ($r->orderFrom !== "all") {
                    $orderQuery->where('ordermaster.orderFrom', $r->orderFrom);
                }
            }

            if ($r->has('storeLocation') && $r->storeLocation != "") {
                if ($r->storeLocation !== "") {
                    $orderQuery->where('ordermaster.storeLocation', $r->storeLocation);
                }
            }

            if ($r->has('phoneno') && $r->phoneno != "") {
                if (strlen($r->phoneno) == 10) {
                    $orderQuery->where('ordermaster.mobileNumber', $r->phoneno);
                }
            }

            if ($r->has('orderStatus') && $r->orderStatus != "") {
                $orderQuery->where("ordermaster.orderStatus", $r->orderStatus);
            } else {
                $orderQuery->whereNotIn("ordermaster.orderStatus", ["delivered", "cancelled"]);
            }

            $getOrder = $orderQuery->orderBy('ordermaster.id', 'DESC')->get();
            if ($getOrder && count($getOrder) > 0) {
                $orderArray = [];

                foreach ($getOrder as $item) {
                    $formattedNumber = "";
                    if ($item->mobileNumber) {
                        $areaCode = substr($item->mobileNumber, 0, 3);
                        $firstPart = substr($item->mobileNumber, 3, 3);
                        $secondPart = substr($item->mobileNumber, 6);
                        $formattedNumber = $areaCode . '-' . $firstPart . '-' . $secondPart;
                    }

                    $data = [
                        "code" => $item->code,
                        "orderFrom" => $item->orderFrom,
                        "orderCode" => $item->orderCode,
                        "receiptNo" => $item->receiptNo,
                        "customerCode" => $item->customerCode,
                        "customerName" => $item->customerName,
                        "customerEmail" => $item->customerEmail,
                        "mobileNumber" => $item->mobileNumber,
                        "formattedNumber" => $formattedNumber ?? "",
                        "address" => $item->address ?? "",
                        "storeLocation" => $item->storeLocation ?? "",
                        "storeName" => $item->storeLocationName,
                        "deliveryType" => $item->deliveryType ?? "",
                        "storeLocationCode" => $item->storeLocationCode ?? "",
                        "cashierCode" => $item->addID ?? "",
                        "cashierName" => $item->CashierName ?? "",
                        "deliveryExecutiveCode" => $item->deliveryExecutiveCode ?? "",
                        "deliveryExecutiveName" => $item->deliveryExecutiveName ?? "",
                        "comments" => $item->comments ?? "",
                        "created_at" => date('d-m-Y h:i A', strtotime($item->created_at)) ?? "",
                        "clientType" => $item->clientType ?? "",
                        "subTotal" => $item->subTotal ?? "0.00",
                        "discountPer" => $item->discountPer ?? "0.00",
                        "discountmount" => $item->discountAmount ?? "0.00",
                        "taxPer" => $item->taxPer ?? "0.00",
                        "taxAmount" => $item->taxAmount ?? "0.00",
                        "grandTotal" => isset($item->grandTotal) ? number_format($item->grandTotal, 2) : "0.00",
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

    public function get_notification_order_list(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'cashierCode' => 'required',
                'orderFrom' => 'required|in:all,store,online',
                'orderStatus' => 'nullable',
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $orderQuery = OrderMaster::join("usermaster as u1", "u1.code", "=", "ordermaster.addID", "left")
                ->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
                ->join("storelocation", "storelocation.code", "=", "ordermaster.storeLocation")
                ->select("ordermaster.*", "u1.username as CashierName", "u2.username as deliveryExecutiveName", "storelocation.storeLocation as storeLocationName")
                ->whereBetween('ordermaster.created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
            //activate when multistore
            //->where("ordermaster.addID", $r->cashierCode);

            if ($r->has('orderFrom') && $r->orderFrom != "") {
                if ($r->orderFrom !== "all") {
                    $orderQuery->where('ordermaster.orderFrom', $r->orderFrom);
                }
            }

            if ($r->has('storeLocation') && $r->storeLocation != "") {
                if ($r->storeLocation !== "") {
                    $orderQuery->where('ordermaster.storeLocation', $r->storeLocation);
                }
            }

            if ($r->has('orderStatus') && $r->orderStatus != "") {
                $orderQuery->where("ordermaster.orderStatus", $r->orderStatus);
            } else {
                $orderQuery->whereNotIn("ordermaster.orderStatus", ["delivered", "cancelled"]);
            }

            $getOrder = $orderQuery->orderBy('ordermaster.id', 'DESC')->limit(10)->get();
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
                        "customerEmail" => $item->customerEmail,
                        "mobileNumber" => $item->mobileNumber,
                        "address" => $item->address ?? "",
                        "storeLocation" => $item->storeLocation ?? "",
                        "storeName" => $item->storeLocationName,
                        "deliveryType" => $item->deliveryType ?? "",
                        "storeLocationCode" => $item->storeLocationCode ?? "",
                        "cashierCode" => $item->addID ?? "",
                        "cashierName" => $item->CashierName ?? "",
                        "deliveryExecutiveCode" => $item->deliveryExecutiveCode ?? "",
                        "deliveryExecutiveName" => $item->deliveryExecutiveName ?? "",
                        "comments" => $item->comments ?? "",
                        "created_at" => date('d-m-Y h:i A', strtotime($item->created_at)) ?? "",
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
            $getOrder = OrderMaster::join("usermaster as u1", "u1.code", "=", "ordermaster.addID", "left")
                ->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
                ->join("storelocation", "storelocation.code", "=", "ordermaster.storeLocation")
                ->select("ordermaster.*", "u1.username as CashierName", "u2.username as deliveryExecutiveName", "storelocation.storeLocation as storeLocationName")
                ->where("ordermaster.code", $r->orderCode)
                ->first();


            $orderItems = [];

            if (!empty($getOrder)) {
                $getOrderItem = OrderLineEntries::select("orderlineentries.*")
                    ->where("orderlineentries.orderCode", $r->orderCode)
                    ->get();

                $productCodeArr = [];
                foreach ($getOrderItem as $item) {
                    if ($item->productType == "side") {
                        $productCodeArr[] = $item->productCode;
                    }
                }

                $sidesArray = [];
                if (sizeof($productCodeArr) > 0) {
                    $sides = DB::table('sidemaster')->select('code', 'type')->whereIn('code', $productCodeArr)->get();
                    if ($sides->count()) {
                        foreach ($sides as $side) {
                            $sidesArray[$side->code] = $side->type;
                        }
                    }
                }

                if ($getOrderItem && count($getOrderItem) > 0) {

                    foreach ($getOrderItem as $item) {
                        $sideType  = "";
                        if ($item->productType == 'side' && sizeof($sidesArray) > 0) {
                            $sideType = $sidesArray[$item->productCode];
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
                            "pizzaSize" => $item->pizzaSize ?? "",
                            "comments" => $item->comments ?? "",
                            "pizzaPrice" => $item->pizzaPrice ?? "0.00",
                            "sideType" => $sideType
                        ];
                        array_push($orderItems, $orderitem);
                    }
                }

                $defaultAddress = '2120 N Park Unit #25, Brampton, ON L6S0CP';
                $store = DB::table("storelocation")->where('code', $getOrder->storeLocation)->first();
                if ($store) {
                    $storeAddress =  $store->storeAddress != "" ? $store->storeAddress : $defaultAddress;
                } else {
                    $storeAddress = $defaultAddress;
                }

                $formattedNumber = "";
                if ($getOrder->mobileNumber) {
                    $areaCode = substr($getOrder->mobileNumber, 0, 3);
                    $firstPart = substr($getOrder->mobileNumber, 3, 3);
                    $secondPart = substr($getOrder->mobileNumber, 6);
                    $formattedNumber = $areaCode . '-' . $firstPart . '-' . $secondPart;
                }

                $data["code"] = $getOrder->code;
                //$data["id"]=$getOrder->pid??"";
                $data["orderCode"] = $getOrder->orderCode ?? "";
                $data["customerCode"] = $getOrder->customerCode ?? "";
                $data["customerName"] = $getOrder->customerName ?? "";
                $data["customerEmail"] = $getOrder->customerEmail ?? "";
                $data["mobileNumber"] = $getOrder->mobileNumber ?? "";
                $data["formattedNumber"] = $formattedNumber ?? "";
                $data["orderFrom"] = $getOrder->orderFrom ?? "store";
                $data["address"] = $getOrder->address ?? "";
                $data["deliveryType"] = $getOrder->deliveryType ?? "";
                $data["storeLocation"] = $getOrder->storeLocationName ?? "";
                $data["storeLocationCode"] = $getOrder->storeLocation ?? "";
                $data["cashierCode"] = $getOrder->addID ?? "";
                $data["cashierName"] = $getOrder->CashierName ?? "";
                $data["deliveryExecutiveCode"] = $getOrder->deliveryExecutiveCode ?? "";
                $data["deliveryExecutiveName"] = $getOrder->deliveryExecutiveName ?? "";
                $data["comments"] = $getOrder->comments ?? "";
                $data["created_at"] = $getOrder->orderDate;
                $data["clientType"] = $getOrder->clientType ?? "";
                $data["subTotal"] = $getOrder->subTotal ?? "0.00";
                $data["discountmount"] = $getOrder->discountAmount ?? "0.00";
                $data["discountPer"] = $getOrder->discountPer ?? "0.00";
                $data["taxAmount"] = $getOrder->taxAmount ?? "0.00";
                $data["taxPer"] = $getOrder->taxPer ?? "0.00";
                $data["deliveryCharges"] = $getOrder->deliveryCharges ?? "0.00";
                $data["extraDeliveryCharges"] = $getOrder->extraDeliveryCharges ?? "0.00";
                $data["grandTotal"] = isset($getOrder->grandTotal) ? number_format($getOrder->grandTotal, 2) : "0.00";
                $data["orderStatus"] = $getOrder->orderStatus;
                $data["orderItems"] = $orderItems;
                $data["zipCode"] = $getOrder->zipCode;
                $data["storeAddress"] = $storeAddress;
                $data['orderTakenBy'] = $getOrder->orderTakenBy;
                $data['isDeliveryTypeChange'] = $getOrder->isDeliveryTypeChange;


                return response()->json(["message" => "Data found", "data" => $data], 200);
            }
            return response()->json(["message" => "Order does not exist"], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function get_delivery_executive_list(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'storeCode' => 'nullable',
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $dataArray = [];
            $query = Users::join("storelocation", "storelocation.code", "=", "usermaster.storeLocationCode", "left")
                ->select("usermaster.*", "storelocation.storeLocation")
                ->where("role", "R_2");
            if ($r->has('storeCode') && trim($r->storeCode) != "") {
                $query->where("storeLocationCode", $r->storeCode);
            }
            $getAllDeliveryExecutive = $query->get();
            if ($getAllDeliveryExecutive && count($getAllDeliveryExecutive) > 0) {
                foreach ($getAllDeliveryExecutive as $item) {
                    $data = ["code" => $item->code, "username" => $item->username, "firstName" => $item->firstname, "middleName" => $item->middlename, "lastName" => $item->lastname, "mobileNumber" => $item->mobile, "storeLocation" => $item->storeLocation ?? "", "storeLocationCode" => $item->storeLocationCode ?? ""];
                    array_push($dataArray, $data);
                }
                return response()->json(["message" => "Data found.", "data" => $dataArray], 200);
            }
            return response()->json(["message" => "Data not found."], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function get_delivery_executive_by_storecode(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'storeCode' => 'required',
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $getDeliveryExecutive = Users::select("usermaster.*")
                ->where("storeLocationCode", $r->storeCode)
                ->where("role", "R_2")
                ->get();
            if ($getDeliveryExecutive && count($getDeliveryExecutive) > 0) {
                foreach ($getDeliveryExecutive as $item) {
                    $data = ["code" => $item->code, "username" => $item->username, "firstName" => $item->firstname, "middleName" => $item->middlename, "lastName" => $item->lastname, "mobileNumber" => $item->mobile];
                    array_push($dataArray, $data);
                }
                return response()->json(["message" => "Data found.", "data" => $dataArray], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function get_previous_order(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'mobileNumber' => 'required',
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $orderQuery = OrderMaster::join("usermaster as u1", "u1.code", "=", "ordermaster.addID", "left")
                ->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
                ->join("storelocation", "storelocation.code", "=", "ordermaster.storeLocation")
                ->select("ordermaster.*", "u1.username as CashierName", "u2.username as deliveryExecutiveName", "storelocation.storeLocation as storeLocationName")
                ->where("ordermaster.mobileNumber", $r->mobileNumber);
            $getOrder = $orderQuery->orderBy('ordermaster.id', 'DESC')
                ->limit(10)->get();
            if ($getOrder  && count($getOrder) > 0) {
                $orderArray = [];
                foreach ($getOrder as $item) {

                    $itemArray = [];

                    $orderItems = OrderLineEntries::select("orderlineentries.*")->where("orderlineentries.orderCode", $item->code)->get();
                    if ($orderItems && count($orderItems) > 0) {
                        foreach ($orderItems as $ordItem) {
                            $orderitem = [
                                "id" => $ordItem->pid,
                                "code" => $ordItem->code,
                                "productCode" => $ordItem->productCode,
                                "productType" => $ordItem->productType,
                                "productName" => $ordItem->productName,
                                "config" => json_decode($ordItem->config, true),
                                "quantity" => $ordItem->quantity ?? "",
                                "price" => $ordItem->price ?? "",
                                "amount" => $ordItem->amount ?? "",
                                "pizzaSize" => $ordItem->pizzaSize ?? "",
                                "pizzaPrice" => $ordItem->pizzaPrice ?? "0.00",
                                "comments" => $ordItem->comments ?? "",
                            ];
                            array_push($itemArray, $orderitem);
                        }
                    }

                    $data = [
                        "code" => $item->code,
                        "orderCode" => $item->orderCode,
                        "comments" => $item->comments ?? "",
                        "created_at" => date('d-m-Y h:i A', strtotime($item->created_at)) ?? "",
                        "grandTotal" => number_format($item->grandTotal, 2) ?? "0.00",
                        "orderItems" => $itemArray
                    ];
                    array_push($orderArray, $data);
                }
                return response()->json(["message" => "Data found", "data" => $orderArray, "values" => $getOrder], 200);
            }
            return response()->json(["message" => "Data not found."], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function update_order_status(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'orderCode' => 'required',
                'orderStatus' => 'required',
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            if ($r->orderStatus == "placed") {
                $getOrderStatus = Ordermaster::where("code", $r->orderCode)->first();
                if (!empty($getOrderStatus) && $getOrderStatus->status == "cancelled") {
                    return response()->json(["status" => 300, "message" => "The order has been canceled already, cannot accept the order after cancellation"], 200);
                }
            }

            if ($r->orderStatus == "shipping") {
                $getOrderStatus = Ordermaster::where("code", $r->orderCode)->first();
                if (!empty($getOrderStatus)) {
                    if ($getOrderStatus->orderFrom == "store" && $getOrderStatus->deliveryType == "pickup") {
                        return response()->json(["status" => 300, "message" => "This status is not for you."], 200);
                    }
                }
            }

            $response = null;
            $socketData = [];
            $updateOrderStatus = Ordermaster::where("code", $r->orderCode)->update(["orderStatus" => $r->orderStatus]);
            if ($updateOrderStatus == true) {
                $order = Ordermaster::where("code",  $r->orderCode)->first();
                if ($order) {
                    $socketData =  [
                        'orderCode' => $order->code,
                        'orderNumber' =>  $order->orderCode,
                        'phoneNumber' => $order->mobileNumber,
                        'status' => $order->orderStatus,
                        'storeCode' => $order->storeLocation,
                        'deliveryType' => $order->deliveryType,
                        "customerName" => $order->customerName,
                        "grandTotal" => $order->grandTotal,
                        "orderFrom" => $order->orderFrom
                    ];

                    if ($order->deliveryType == "pickup") {
                        // $uri = config('constant.SITE_MODE') == 'LIVE' ? config('constant.SOCKET_URL_LIVE') : config('constant.SOCKET_URL_TEST');
                        // $uri .= '/order/status/change';
                        // $response = Http::get($uri, [
                        //   'orderCode' => $order->code,
                        //   'orderNumber' =>  $order->orderCode,
                        //   'phoneNumber' => $order->mobileNumber,
                        //   'status' => $order->orderStatus,
                        //   'storeCode' => $order->storeLocation,
                        //   'deliveryType' => $order->deliveryType,
                        //   "customerName" => $order->customerName,
                        //   "grandTotal" => $order->grandTotal,
                        //   "orderFrom" => $order->orderFrom
                        // ]);
                        // if ($response->status() == 200) {
                        //   $response = $uri;
                        // }


                    }
                }
                return response()->json(["status" => 200, "message" => "Order Status changed successfully.", "data" => $socketData], 200);
            }
            return response()->json(["status" => 300, "message" => "Failed to changed order status."], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    // Order Screens for pickup orders only
    public function get_placed_or_ready_orders(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'storeLocation' => 'required'
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $orders = Ordermaster::where("storeLocation", $r->storeLocation)->whereIn('orderStatus', ['placed', 'ready'])->where('deliverytype', 'pickup')->orderBy('id', 'DESC')->get();
            $data = [];
            if ($orders->count() > 0) {
                for ($i = 0; $i < $orders->count(); $i++) {
                    $order = $orders[$i];
                    array_push($data, [
                        'orderCode' => $order->code,
                        'orderNumber' =>  $order->orderCode,
                        'phoneNumber' => $order->mobileNumber,
                        'status' => $order->orderStatus,
                        'storeCode' => $order->storeLocation,
                        'deliveryType' => $order->deliveryType,
                        "customerName" => $order->customerName,
                        "grandTotal" => $order->grandTotal,
                        "orderFrom" => $order->orderFrom
                    ]);
                }
            }
            return response()->json(['message' => 'Success', 'data' => $data], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    // New Orders accept screen with loop bell ring
    public function get_orders_for_accept_screen(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'storeLocation' => 'required'
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $orders = Ordermaster::where("storeLocation", $r->storeLocation)->whereIn('orderStatus', ['pending'])->where('orderFrom', 'online')->orderBy('id', 'DESC')->get();
            $data = [];
            if ($orders->count() > 0) {
                for ($i = 0; $i < $orders->count(); $i++) {
                    $order = $orders[$i];
                    array_push($data, [
                        'orderCode' => $order->code,
                        'orderNumber' =>  $order->orderCode,
                        'phoneNumber' => $order->mobileNumber,
                        'status' => $order->orderStatus,
                        'storeCode' => $order->storeLocation,
                        'deliveryType' => $order->deliveryType,
                        "customerName" => $order->customerName,
                        "grandTotal" => $order->grandTotal,
                        "orderFrom" => $order->orderFrom
                    ]);
                }
            }
            return response()->json(['message' => 'Success', 'data' => $data], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function update_delivery_type(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'orderCode' => 'required',
                'customerName' => 'nullable',
                'postalcode' => 'nullable',
                'address' => 'nullable',
                'deliveryExecutiveCode' => 'nullable',
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $updateOrderStatus = false;
            $deliveryCharges = DB::table('settings')->where('code', 'STG_1')->first();
            $getOrderStatus = Ordermaster::where("code", $r->orderCode)->first();

            if ($getOrderStatus) {
                if ($getOrderStatus->isDeliveryTypeChange != 1) {
                    if ($getOrderStatus->orderStatus == "placed") {
                        // For Pickup to Delivery
                        if ($getOrderStatus->deliveryType == "pickup") {
                            $isDelivarable = DB::table("zipcode")->where('isActive', 1)->where('zipcode', $r->postalcode)->first();

                            if (!empty($isDelivarable)) {
                                $newGrandTotal = $getOrderStatus->grandTotal + $deliveryCharges->settingValue + $r->extraDeliveryCharges;
                                $updateOrderStatus = Ordermaster::where("code", $r->orderCode)->update([
                                    "deliveryType" => "delivery",
                                    "address" => $r->address,
                                    "deliveryExecutiveCode" => $r->deliveryExecutiveCode,
                                    "customerName" => $r->customerName,
                                    "zipCode" => $r->postalcode,
                                    "deliveryCharges" => $deliveryCharges->settingValue,
                                    "grandTotal" => $newGrandTotal,
                                    "isDeliveryTypeChange" => 1
                                ]);
                            } else {
                                return response()->json(["delivarable" => false, $isDelivarable], 200);
                            }
                        }
                        // For Delivery to Pickup
                        if ($getOrderStatus->deliveryType == "delivery") {
                            $charges = $deliveryCharges->settingValue + $getOrderStatus->extraDeliveryCharges;
                            $newGrandTotal = $getOrderStatus->grandTotal - $charges;
                            $updateOrderStatus = Ordermaster::where("code", $r->orderCode)->update([
                                "deliveryType" => "pickup",
                                "isDeliveryTypeChange" => 1,
                                "grandTotal" => $newGrandTotal,
                                "deliveryCharges" => 0,
                                "extraDeliveryCharges" => 0,
                            ]);
                        }
                        if ($updateOrderStatus == true) {
                            return response()->json(["message" => "Delivery Type Updated Successfully.", "data" => $updateOrderStatus], 200);
                        }
                    }
                    return response()->json(["message" => `Your Status is alredy $getOrderStatus->orderStatus, Not able to change delivery type`], 400);
                }
                return response()->json(["message" => "Delivery type already changed."], 400);
            }
            return response()->json(["message" => "Failed to changed delivery type."], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function direct_update_delivery_type(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'orderCode' => 'required',
                'customerName' => 'nullable',
                'postalcode' => 'nullable',
                'address' => 'nullable',
                'deliveryExecutiveCode' => 'nullable',
                'extraDeliveryCharges' => 'nullable',
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $updateOrderStatus = false;
            $deliveryCharges = DB::table('settings')->where('code', 'STG_1')->first();
            $getOrderStatus = Ordermaster::where("code", $r->orderCode)->first();

            if ($getOrderStatus) {
                if ($getOrderStatus->isDeliveryTypeChange != 1) {
                    if ($getOrderStatus->orderStatus == "placed") {
                        // For Pickup to Delivery
                        if ($getOrderStatus->deliveryType == "pickup") {
                            $newGrandTotal = $getOrderStatus->grandTotal + $deliveryCharges->settingValue + $r->extraDeliveryCharges;
                            $updateOrderStatus = Ordermaster::where("code", $r->orderCode)->update([
                                "deliveryType" => "delivery",
                                "address" => $r->address,
                                "deliveryExecutiveCode" => $r->deliveryExecutiveCode,
                                "customerName" => $r->customerName,
                                "zipCode" => $r->postalcode,
                                "deliveryCharges" => $deliveryCharges->settingValue,
                                "extraDeliveryCharges" => $r->extraDeliveryCharges,
                                "grandTotal" => $newGrandTotal,
                                "isDeliveryTypeChange" => 1
                            ]);
                        }
                        // For Delivery to Pickup
                        if ($getOrderStatus->deliveryType == "delivery") {
                            $charges = $deliveryCharges->settingValue + $getOrderStatus->extraDeliveryCharges;
                            $newGrandTotal = $getOrderStatus->grandTotal - $charges;
                            $updateOrderStatus = Ordermaster::where("code", $r->orderCode)->update([
                                "deliveryType" => "pickup",
                                "isDeliveryTypeChange" => 1,
                                "grandTotal" => $newGrandTotal,
                                "deliveryCharges" => 0,
                                "extraDeliveryCharges" => 0,
                            ]);
                        }
                        if ($updateOrderStatus == true) {
                            return response()->json(["message" => "Delivery Type Updated Successfully.", "data" => $updateOrderStatus], 200);
                        }
                    }
                    return response()->json(["message" => `Your Status is alredy $getOrderStatus->orderStatus, Not able to change delivery type`], 400);
                }
                return response()->json(["message" => "Delivery type already changed."], 400);
            }
            return response()->json(["message" => "Failed to changed delivery type."], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    // Developer: Shreyas Mahamuni, Wokring Time: 08-12-2023
    // This Function Add credit comment in ordermaster table
    public function add_credit_comments(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'orderCode' => 'required',
                'creditComment' => 'required',
            ]);

            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            if ($r->creditComment != "") {
                $addCreditComment = Ordermaster::where("code", $r->orderCode)->update([
                    "comments" => $r->creditComment,
                ]);
                if ($addCreditComment == true) {
                    return response()->json(["message" => "Credit Comment Added Successfully.", "data" => $addCreditComment], 200);
                }
            }
            return response()->json(["message" => "Failed to add Credit Comment."], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    /**
     * Order Summary for Telecashier
     */
    public function order_summary(Request $r)
    {
        try {

            $query = DB::table('ordermaster as om')
                ->join('storelocation as sl', 'om.storeLocation', '=', 'sl.code')
                ->select(
                    'sl.storeLocation as store_name',
                    DB::raw('COUNT(om.id) as total_orders'),
                    DB::raw('SUM(om.grandTotal) as total_amount'),
                    DB::raw("SUM(CASE WHEN om.orderFrom = 'online' THEN 1 ELSE 0 END) as online_orders"),
                    DB::raw("SUM(CASE WHEN om.orderFrom = 'store' THEN 1 ELSE 0 END) as in_store_orders")
                )
                ->where('om.orderStatus', '!=', 'cancelled');

            if ($r->has('filter_date') && $r->filter_date != "") {
                $curDate = date('Y-m-d', strtotime($r->filter_date));
            } else {
                $curDate = date('Y-m-d');
            }

            $query->whereBetween('om.orderDate', ["$curDate 00:00:00", "$curDate 23:59:59"]);

            if ($r->has('filter_store') && $r->filter_store != "") {
                $query->where('om.storeLocation', $r->filter_store);
            }

            $ordersSummary = $query->groupBy('sl.storeLocation')->get();

            foreach ($ordersSummary as $item) {
                $item->total_amount = number_format($item->total_amount, 2);
            }

            return response()->json(["message" => "Data found", "data" => $ordersSummary], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }
}
