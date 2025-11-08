<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Models\GlobalModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Users;
use App\Models\Customer;
use App\Models\CashierCartMaster;
use App\Models\CashierCartLineEntries;
use App\Models\OrderMaster;
use App\Models\OrderLineEntries;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Config;


class InvoicesController extends Controller
{
	private $role, $rights;
	public function __construct(GlobalModel $model)
	{
		$this->model = $model;
	}

	public function getListByMobileNumber(Request $req)
	{
		try {
			$mobileNumber = $req->mobileNumber;
			$search = $req->search;
			$tableName = "customer";
			$orderColumns = array("customer.*");
			$condition = array('customer.mobileNumber' => array('=', $mobileNumber));
			$orderBy = array('customer' . '.id' => 'DESC');
			$join = array();
			$like = array('customer.mobileNumber' => $search);
			$limit = $req->length;
			$offset = $req->start;
			$extraCondition = "";
			$result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
			return response()->json(["message" => "Data found", "data" => $result], 200);
		} catch (\Exception $ex) {
			return response()->json(['message' => $ex->getMessage()], 400);
		}
	}

	public function getInvoice(Request $r)
	{
		try {
			$input = $r->all();
			$validator = Validator::make($input, [
				'cashierCode' => 'nullable',
				'orderId' => 'nullable',
				'deliveryType' => 'nullable',
				'mobileNumber' => 'nullable',
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
					$toDate = $toDate . " 23:59:59";
				}
			}

			$getCount = OrderMaster::join("usermaster as u1", "u1.code", "=", "ordermaster.addID", "left")
				->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
				->join("storelocation", "storelocation.code", "=", "ordermaster.storeLocation")
				->select("ordermaster.*", "u1.username as CashierName", "u2.username as deliveryExecutiveName", "storelocation.storeLocation as storeLocationName")
				->whereBetween('ordermaster.created_at', [$fromdate, $todate]);
			if ($r->has('cashierCode') && $r->cashierCode != "") {
				$getCount->where('ordermaster.addID', $r->cashierCode);
			}
			if ($r->has('orderId') && $r->orderId != "") {
				$getCount->where('ordermaster.code', $r->orderId);
			}
			if ($r->has('deliveryType') && $r->deliveryType != "") {
				$getCount->where('ordermaster.deliveryType', $r->deliveryType);
			}
			if ($r->has('mobileNumber') && $r->mobileNumber != "") {
				$getCount->where('ordermaster.mobileNumber', $r->mobileNumber);
			}
			if ($r->has('storeLocation') && $r->storeLocation != "") {
				if ($r->storeLocation !== "") {
					$getCount->where('ordermaster.storeLocation', $r->storeLocation);
				}
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

			$orderQuery = OrderMaster::join("usermaster as u1", "u1.code", "=", "ordermaster.addID", "left")
				->join("usermaster as u2", "u2.code", "=", "ordermaster.deliveryExecutiveCode", "left")
				->join("storelocation", "storelocation.code", "=", "ordermaster.storeLocation")
				->select("ordermaster.*", "u1.username as CashierName", "u2.username as deliveryExecutiveName", "storelocation.storeLocation as storeLocationName")
				->whereBetween('ordermaster.created_at', [$fromdate, $todate]);
			if ($r->has('cashierCode') && $r->cashierCode != "") {
				$orderQuery->where('ordermaster.addID', $r->cashierCode);
			}
			if ($r->has('orderId') && $r->orderId != "") {
				$orderQuery->where('ordermaster.code', $r->orderId);
			}
			if ($r->has('deliveryType') && $r->deliveryType != "") {
				$orderQuery->where('ordermaster.deliveryType', $r->deliveryType);
			}
			if ($r->has('mobileNumber') && $r->mobileNumber != "") {
				$orderQuery->where('ordermaster.mobileNumber', $r->mobileNumber);
			}
			if ($r->has('storeLocation') && $r->storeLocation != "") {
				if ($r->storeLocation !== "") {
					$orderQuery->where('ordermaster.storeLocation', $r->storeLocation);
				}
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
						"receiptNo" => $item->receiptNo,
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
						"orderTakenBy" => $item->orderTakenBy ?? ""
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
}
