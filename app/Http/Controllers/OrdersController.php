<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\OrderMaster;
use App\Models\OrderLineEntries;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;
use PDF;
use App\Services\DoorDashService;
use App\Models\DoorDashStep;
use App\Models\Business;
use App\Classes\Twilio;

class OrdersController extends Controller
{
    private $role, $rights;
    protected DoorDashService $doorDashService;
    public function __construct(GlobalModel $model,DoorDashService $doorDashService)
    {
        $this->model = $model;
        $this->doorDashService = $doorDashService;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('9.1', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    public function getOrders(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('ordermaster.code' => $search);
        $condition = array();
        $orderBy = array('ordermaster' . '.id' => 'DESC');
        $result = $this->model->selectQuery('ordermaster.*', 'ordermaster', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => $item->code);
            }
        }
        echo  json_encode($html);
    }

    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            return view('orders.list');
        } else {
            return view('noright');
        }
    }

    public function getOrdersList(Request $req)
    {
        $orderStatus = $req->orderstatus;
        $orders = $req->orders;
        $orderfrom = $req->orderfrom;
        $search = $req->input('search.value');
        $tableName = "ordermaster";
        $orderColumns = array("ordermaster.*", "storelocation.storeLocation");
        $condition = array('ordermaster.code' => array('=', $orders), 'ordermaster.orderFrom' => array('=', $orderfrom), 'ordermaster.orderStatus' => array('=', $orderStatus));
        $orderBy = array('ordermaster' . '.id' => 'DESC');
        $groupBy = array();
        $join = array('storelocation' => array('storelocation.code', 'ordermaster.storeLocation'));
        $joinType = array('storelocation' => 'left');
        $like = array('ordermaster.zipCode' => $search, 'ordermaster.clientType' => $search, 'ordermaster.code' => $search, 'ordermaster.customerName' => $search, 'ordermaster.mobileNumber' => $search, 'ordermaster.orderFrom' => $search, 'storelocation.storeLocation' => $search, 'ordermaster.grandTotal' => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQueryWithGroupBy($orderColumns, $tableName, $join, $condition, $orderBy, $groupBy, $like, $limit, $offset, $extraCondition, $joinType);
        $srno = $_GET['start'] + 1;
        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
                $role = '';
                $status = "";
                if ($row->orderStatus == "delivered") {
                    $status = '<span class="badge badge-success">Delivered</span>';
                }
                if ($row->orderStatus == "placed") {
                    $status = '<span class="badge badge-info">Placed</span>';
                }
                if ($row->orderStatus == "shipping") {
                    $status = '<span class="badge badge-info">Shipping</span>';
                }
                if ($row->orderStatus == "picked-up") {
                    $status = '<span class="badge badge-info">Picked-up</span>';
                }
                if ($row->orderStatus == "cancelled") {
                    $status = '<span class="badge badge-danger">Cancelled</span>';
                }
                if ($row->orderStatus == "pending") {
                    $status = '<span class="badge badge-danger">Pending</span>';
                }

                $payment_status = "";
                if ($row->clientType == "customer") {
                    if ($row->paymentStatus == "paid") {
                        $payment_status = '<span class="badge badge-success">Paid</span>';
                    }
                    if ($row->paymentStatus == "failed") {
                        $payment_status = '<span class="badge badge-danger">Failed</span>';
                    }
                    if ($row->paymentStatus == "pending") {
                        $payment_status = '<span class="badge badge-info">Pending</span>';
                    }
                    if ($row->paymentStatus == "cancelled") {
                        $payment_status = '<span class="badge badge-danger">Cancelled</span>';
                    }
                }

                $actions = '<div class="btn-group">
                <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ti-settings"></i>
                </button>
                <div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">';
                if ($this->rights != '' && $this->rights['view'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("orders/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                $actions .= '</div>
                </div>';
                $data[] = array(
                    $srno,
                    $actions,
                    $row->orderCode,
                    date('d/M/Y h:i A', strtotime($row->orderDate)),
                    $row->code,
                    $row->customerName,
                    $row->mobileNumber,
                    $row->zipCode,
                    $row->orderFrom,
                    $row->storeLocation,
                    $row->grandTotal,
                    $status,
                    $payment_status,
                    $row->clientType,
                );
                $srno++;
            }
            $dataCount = OrderMaster::leftJoin("storelocation", "storelocation.code", "=", "ordermaster.storeLocation");

            if (!empty($orders)) {
                $dataCount = $dataCount->where("ordermaster.code", $orders);
            }

            if (!empty($orderfrom)) {
                $dataCount = $dataCount->where("ordermaster.orderFrom", $orderfrom);
            }

            if (!empty($orderStatus)) {
                $dataCount = $dataCount->where("ordermaster.orderStatus", $orderStatus);
            }

            $dataCount = $dataCount->where(function ($query) use ($search) {
                $query->where("ordermaster.zipCode", "LIKE", "%$search%")
                    ->orWhere("ordermaster.clientType", "LIKE", "%$search%")
                    ->orWhere("ordermaster.code", "LIKE", "%$search%")
                    ->orWhere("ordermaster.customerName", "LIKE", "%$search%")
                    ->orWhere("ordermaster.mobileNumber", "LIKE", "%$search%")
                    ->orWhere("ordermaster.orderFrom", "LIKE", "%$search%")
                    ->orWhere("storelocation.storeLocation", "LIKE", "%$search%")
                    ->orWhere("ordermaster.grandTotal", "LIKE", "%$search%");
            });

            $dataCount = $dataCount->count();
        }
        $output = array(
            "draw" => intval($_GET["draw"]),
            "recordsTotal" => $dataCount,
            "recordsFiltered" => $dataCount,
            "data" => $data
        );
        echo json_encode($output);
    }

    public function view(Request $r)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $r->code;
            $orders = OrderMaster::select('ordermaster.*', "storelocation.storeLocation", "usermaster.username")
                ->join("usermaster", "usermaster.code", "=", "ordermaster.deliveryExecutiveCode", "left")
                ->join("storelocation", "storelocation.code", "=", "ordermaster.storeLocation", "left")
                ->where('ordermaster.code', $code)->first();
            if (!empty($orders)) {
                $data['orderlineentries'] = OrderLineEntries::select("orderlineentries.*")
                    ->where("orderlineentries.orderCode", $code)
                    ->get();

                $data['queryresult'] = $orders;
                return view('orders.view', $data);
            }
        } else {
            return view('noright');
        }
    }

    public function getInvoice(Request $r)
    {

        $code = $r->code;
        $data['orders'] = OrderMaster::select('ordermaster.*', "storelocation.storeLocation", "storelocation.storeAddress as storeAddress", "usermaster.username")
            ->join("usermaster", "usermaster.code", "=", "ordermaster.deliveryExecutiveCode", "left")
            ->join("storelocation", "storelocation.code", "=", "ordermaster.storeLocation", "left")
            ->where('ordermaster.code', $code)->first();
        $data['orderlineentries'] = OrderLineEntries::select("orderlineentries.*")
            ->where("orderlineentries.orderCode", $code)
            ->get();
        $pdf = PDF::loadView('orders.invoice', $data);
        return $pdf->stream();
    }


    public function updateOrderStatus(Request $r)
    {
        $orderCode = $r->orderCode;
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();

        $getOrderStatus = DB::table("ordermaster")
            ->where("code", $orderCode)
            ->first();

        if (!$getOrderStatus) {
            return response()->json(["status" => "failed", "message" => "Order not found"], 200);
        }

        if ($getOrderStatus->orderStatus == "shipping") {
            if (in_array($r->orderStatus, ["cancelled", "placed"])) {
                return response()->json(["status" => "failed", "message" => "You are not allowed to change status."], 200);
            }
        }

        if ($getOrderStatus->orderStatus == "delivered") {
            if (in_array($r->orderStatus, ["cancelled", "placed", "shipping"])) {
                return response()->json(["status" => "failed", "message" => "You are not allowed to change status."], 200);
            }
        }

        if ($getOrderStatus->orderStatus == "picked-up") {
            if (in_array($r->orderStatus, ["cancelled", "placed"])) {
                return response()->json(["status" => "failed", "message" => "You are not allowed to change status."], 200);
            }
        }

        /* ===========================
       DOORDASH CANCEL DELIVERY
    ============================ */

        if (
            $r->orderStatus == "cancelled" &&
            $getOrderStatus->deliveryType == "delivery" &&
            $getOrderStatus->doordash_status == "QUOTE_ACCEPTED" &&
            !empty($getOrderStatus->doordash_delivery_id)
        ) {

            try {
                $doordash = new DoorDashService;
                $doordash->cancelDelivery($getOrderStatus->doordash_delivery_id);

            } catch (\Exception $e) {
                Log::error("DoorDash cancel failed", [
                    "orderCode" => $orderCode,
                    "error" => $e->getMessage()
                ]);

                return response()->json([
                    "status" => "failed",
                    "message" => "Failed to cancel DoorDash delivery"
                ], 200);
            }
        }

        $data = [
            'orderStatus' => $r->orderStatus,
            'updated_at' => $currentdate
        ];

        $result = $this->model->doEditWithField($data, 'ordermaster', 'code', $orderCode);

        $datastring = $currentdate->toDateTimeString() . "	" . $ip . "	" . Auth::guard('admin')->user()->code . "	Order status " . $orderCode . " is updated.";
        $this->model->activity_log($datastring);

        if ($result) {
            return response()->json(["status" => "success"], 200);
        }

        return response()->json(["status" => "failed", "message" => "Failed to update status of order."], 200);
    }
}
