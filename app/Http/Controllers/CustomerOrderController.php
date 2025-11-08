<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\ApiModel;
use App\Models\Users;
use App\Models\Customer;
use App\Models\OrderMaster;
use App\Models\OrderLineEntries;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Config;
use Stripe;
use Stripe\WebhookEndpoint;
use Illuminate\Support\Facades\Log;

class CustomerOrderController extends Controller
{
	public function __construct(GlobalModel $model, ApiModel $apimodel)
	{
		$this->model = $model;
		$this->apimodel = $apimodel;
	}

	public function order_place(Request $r)
	{
		try {
			$currentdate = Carbon::now();
			$now =  $currentdate->toDateTimeString();
			$date =  $currentdate->toDate();
			$input = $r->all();
			$rules = [
				'customerCode' 			    => 'nullable',
				'mobileNumber' 			    => ['required', 'numeric'],
				//'deliveryType' 			    => 'required|delivery',
				'products' 				    => 'required|array|min:1',
				'products.*.id'             => 'required',
				'products.*.productCode'    => 'required',
				'products.*.productName'    => 'required',
				'products.*.productType'    => 'required',
				//'products.*.config'         => 'required',
				'products.*.quantity'       => 'required',
				'products.*.price'          => 'required',
				'products.*.amount'         => 'required',
				'subTotal' 				    => 'required',
				'discountAmount' 		    => 'nullable',
				'taxPer' 				    => 'nullable',
				'taxAmount' 			    => 'nullable',
				'deliveryCharges' 		    => 'nullable',
				'extraDeliveryCharges' 	    => 'nullable',
				'grandTotal' 			    => 'required',
				'address'                   => 'required|min:10|max:400',
				'zipCode'                   => 'required|regex:/^[ABCEGHJKLMNPRSTVXY]\d[A-Z]\d[A-Z]\d$/i',
				//'deliveryExecutive'       => 'required',
				'customerName'              => 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/',
			];

			$messages = [
				//'customerCode.required' 			=> 'Customer is required', 
				'mobileNumber.required' 		    => 'Phone number is required',
				'mobileNumber.numeric' 			    => 'Phone number is invalid',
				//'deliveryType.required' 		    => 'Delivery Type is required',
				//'deliveryType.in' 			        => 'Delivery Type must be delivery',
				'products.required' 		        => 'Cart is empty, cannot place the order',
				'products.array' 				    => 'Cart is Invalid',
				'products.min' 				        => 'Cart must have at-least one product/item',
				'subTotal.required' 			    => 'Subtotal is missing',
				'grandTotal.required' 			    => 'Grand total is missing',
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
				"orderStatus" => "placed",
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
						$orderLineEntries = [
							"pid" => $item["id"],
							"orderCode" => $order,
							"productCode" => $item["productCode"],
							"productName" => $item["productName"],
							"productType" => $item["productType"],
							"config" => json_encode($item["config"]),
							"quantity" => $item["quantity"],
							"price" => $item["price"],
							"amount" => $item["amount"],
							"pizzaSize" => $item["pizzaSize"],
							"comments" => $item["comments"] ?? "",
							"created_at" => $now,
							"pizzaPrice" => $item['pizzaPrice'] ?? "0.00"
						];
						$this->model->addNew($orderLineEntries, "orderlineentries", "ORDL");
					}

					return response()->json([
						"message" => "Order place successfully.",
						"orderCode" => $order,
						"receiptNo" => $receiptNo,
						"txnId" => $txnId,
						"orderDate" => $now,
						"totalAmount" => $r->grandTotal
					], 200);
				}
				return response()->json(["message" => "Failed to place order."], 400);
			}
			return response()->json(["message" => "Failed to place order."], 400);
		} catch (\Exception $ex) {
			return response()->json(['message' => $ex->getMessage()], 400);
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
				'customerCode' 			    => 'nullable',
				'mobileNumber' 			    => ['required', 'numeric'],
				//'deliveryType' 			    => 'required|delivery',
				'products' 				    => 'required|array|min:1',
				'products.*.id'             => 'required',
				'products.*.productCode'    => 'required',
				'products.*.productName'    => 'required',
				'products.*.productType'    => 'required',
				//'products.*.config'         => 'required',
				'products.*.quantity'       => 'required',
				'products.*.price'          => 'required',
				'products.*.amount'         => 'required',
				'subTotal' 				    => 'required',
				'discountAmount' 		    => 'nullable',
				'taxPer' 				    => 'nullable',
				'taxAmount' 			    => 'nullable',
				'deliveryCharges' 		    => 'nullable',
				'extraDeliveryCharges' 	    => 'nullable',
				'grandTotal' 			    => 'required',
				'address'                   => 'required|min:10|max:400',
				'zipCode'                   => 'required|regex:/^[ABCEGHJKLMNPRSTVXY]\d[A-Z]\d[A-Z]\d$/i',
				//'deliveryExecutive'       => 'required',
				'customerName'              => 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/',
				'callbackUrl'               => 'required',
				'cancelUrl'                 => 'required'
			];

			$messages = [
				//'customerCode.required' 			=> 'Customer is required', 
				'mobileNumber.required' 		    => 'Phone number is required',
				'mobileNumber.numeric' 			    => 'Phone number is invalid',
				//'deliveryType.required' 		    => 'Delivery Type is required',
				//'deliveryType.in' 			        => 'Delivery Type must be delivery',
				'products.required' 		        => 'Cart is empty, cannot place the order',
				'products.array' 				    => 'Cart is Invalid',
				'products.min' 				        => 'Cart must have at-least one product/item',
				'subTotal.required' 			    => 'Subtotal is missing',
				'grandTotal.required' 			    => 'Grand total is missing',
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
						$orderLineEntries = [
							"pid" => $item["id"],
							"orderCode" => $order,
							"productCode" => $item["productCode"],
							"productName" => $item["productName"],
							"productType" => $item["productType"],
							"config" => json_encode($item["config"]),
							"quantity" => $item["quantity"],
							"price" => $item["price"],
							"amount" => $item["amount"],
							"pizzaSize" => $item["pizzaSize"],
							"comments" => $item["comments"] ?? "",
							"created_at" => $now,
							"pizzaPrice" => $item['pizzaPrice'] ?? "",
						];
						$this->model->addNew($orderLineEntries, "orderlineentries", "ORDL");
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
						'success_url' => $r['callbackUrl'],
						'cancel_url' => $r['cancelUrl']
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

			$getCount = OrderMaster::join("customer as u1", "u1.code", "=", "ordermaster.addID", "left")
				->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
				->select("ordermaster.*", "u1.fullName as customerName", "u2.username as deliveryExecutiveName")
				->where("ordermaster.customerCode", $r->customerCode);
			if ($r->fromDate != "" && $r->toDate != "") {
				$getCount->whereBetween('ordermaster.created_at', [$fromdate, $todate]);
			}
			if ($r->has('transactionId') && $r->transactionId != "") {
				$getCount->where('ordermaster.txnId', $r->transactionId);
			}
			$count = $getCount->orderBy('ordermaster.id', 'DESC')->count();
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

			$orderQuery = OrderMaster::join("customer as u1", "u1.code", "=", "ordermaster.addID", "left")
				->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
				->select("ordermaster.*", "u1.fullName as customerName", "u2.username as deliveryExecutiveName")
				->where("ordermaster.customerCode", $r->customerCode);
			if ($r->fromDate != "" && $r->toDate != "") {
				$orderQuery->whereBetween('ordermaster.created_at', [$fromdate, $todate]);
			}
			if ($r->has('transactionId') && $r->transactionId != "") {
				$orderQuery->where('ordermaster.txnId', $r->transactionId);
			}
			$getOrder = $orderQuery->orderBy('ordermaster.id', 'DESC')
				->skip($offset)
				->limit($perpage)
				->get();

			if ($getOrder && count($getOrder) > 0) {
				$orderArray = [];
				foreach ($getOrder as $item) {
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
						"clientType" => $item->clientType ?? "",
						"subTotal" => $item->subTotal ?? "0.00",
						"discountPer" => $item->discountPer ?? "0.00",
						"discountmount" => $item->discountAmount ?? "0.00",
						"taxPer" => $item->taxPer ?? "0.00",
						"taxAmount" => $item->taxAmount ?? "0.00",
						"grandTotal" => $item->grandTotal ?? "0.00",
						"orderStatus" => $item->orderStatus,
					];
					array_push($orderArray, $data);
				}
				return response()->json(["message" => "Data found", "currentPage" => $page, "perPage" => $perpage, "totalPages" => $totalPages, "totalCount" => $count, "data" => $orderArray], 200);
			}
			return response()->json(["message" => "Data not found."], 200);
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
				->select("ordermaster.*", "u1.fullName as customerName", "u2.username as deliveryExecutiveName")
				->where("ordermaster.code", $r->orderCode)
				->first();

			$orderItems = [];
			if (!empty($getOrder)) {
				$getOrderItem = OrderLineEntries::select("orderlineentries.*")
					->where("orderlineentries.orderCode", $r->orderCode)
					->get();
				if ($getOrderItem && count($getOrderItem) > 0) {

					foreach ($getOrderItem as $item) {
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
							"pizzaPrice" => $item->pizzaPrice ?? "0.00"
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
				$data["created_at"] = $getOrder->created_at;
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
				$data["zipCode"] = $getOrder->zipCode;
				$data["storeAddress"] = $storeAddress;
				$data["storeLocation"] = $store->storeLocation;

				return response()->json(["message" => "Data found", "data" => $data, $getOrder], 200);
			}
			return response()->json(["message" => "Order does not exist"], 200);
		} catch (\Exception $ex) {
			return response()->json(['message' => $ex->getMessage()], 400);
		}
	}

	public function webhook(Request $r)
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
						$data['paymentOrderId'] = $orderId;
						$data['orderStatus'] = 'placed';
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

	public function payment_success(Request $r)
	{
		return response()->json(["status" => 200, "message" => "Payment Successfull."]);
	}

	public function payment_failed(Request $r)
	{
		return response()->json(["status" => 300, "message" => "Payment Failed."]);
	}
}
