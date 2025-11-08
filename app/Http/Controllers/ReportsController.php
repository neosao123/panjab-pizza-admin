<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;

class ReportsController extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('10.1', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }
    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            return view('reports.list');
        } else {
            return view('noright');
        }
    }


    public function getReportsList(Request $req)
    {
        $fromDate = date('Y-m-d 00:00:00');
        $toDate = date('Y-m-d 23:59:59');

        DB::enableQueryLog();

        $search = $limit = $offset = '';
        $srno = 1;
        $draw = 0;

        $export = $req->export;
        if ($export == 0) {
            $search = $req->input('search.value');
            $limit = $req->length;
            $offset = $req->start;
            $srno = $_GET['start'] + 1;
            $draw = $_GET["draw"];
        }

        if ($req->fromDate != "" && $req->toDate != "") {
            $fromDate = date('Y-m-d 00:00:00', strtotime(str_replace('/', '-', $req->fromDate)));
            $toDate = date('Y-m-d 23:59:59', strtotime(str_replace('/', '-', $req->toDate)));
        }

        $deliveryType = $req->deliveryType;
        $orderfrom = $req->orderfrom;
        $orderno = $req->orderno;
        $tableName = "ordermaster";
        $orderColumns = array("ordermaster.*", "storelocation.storeLocation");
        $condition = array('ordermaster.orderFrom' => array('=', $orderfrom), 'ordermaster.deliveryType' => array('=', $deliveryType), 'ordermaster.code' => array('=', $orderno));
        $orderBy = array('ordermaster' . '.id' => 'DESC');
        $groupBy = array();
        $join = array('storelocation' => array('storelocation.code', 'ordermaster.storeLocation'));
        $joinType = array('storelocation' => 'left');
        $like = array('ordermaster.orderFrom' => $search, 'ordermaster.deliveryType' => $search, 'ordermaster.orderCode' => $search, 'ordermaster.storeLocation' => $search);
        $extraCondition = "ordermaster.created_at BETWEEN '$fromDate' AND '$toDate'";
        $result = $this->model->selectQueryWithGroupBy($orderColumns, $tableName, $join, $condition, $orderBy, $groupBy, $like, $limit, $offset, $extraCondition, $joinType);
        $r = DB::getQueryLog();
        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
                $role = '';
                $status = "";
                if ($export == 0) {
                    if ($row->orderFrom == "store") {
                        $status = '<span class="badge badge-success">Store</span>';
                    } else {
                        $status = '<span class="badge badge-primary">Online</span>';
                    }
                } else {
                    $status = ucfirst($row->orderFrom);
                }
                $data[] = array(
                    $srno,
                    $row->storeLocation,
                    $row->orderCode,
                    $row->deliveryType,
                    $status,
                    $row->grandTotal,
                );
                $srno++;
            }
            $dataCount = sizeof($this->model->selectQueryWithGroupBy($orderColumns, $tableName,  $join, $condition, $orderBy, $groupBy, $like, '', '', $extraCondition, $joinType));
        }
        $output = array(
            "draw" => intval($draw),
            "recordsTotal" => $dataCount,
            "recordsFiltered" => $dataCount,
            "data" => $data,
            "r" => $r
        );
        echo json_encode($output);
    }

    public function reportsByStoreLocation()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['storelocation'] = DB::table('storelocation')->get();
            return view('reports.list-store-location', $data);
        } else {
            return view('noright');
        }
    }

    public function getReportsListByStoreLocation(Request $req)
    {
        $fromDate = date('Y-m-d 00:00:00');
        $toDate = date('Y-m-d 23:59:59');

        DB::enableQueryLog();

        $search = $limit = $offset = '';
        $srno = 1;
        $draw = 0;

        if ($req->fromDate != "" && $req->toDate != "") {
            $fromDate = date('Y-m-d 00:00:00', strtotime(str_replace('/', '-', $req->fromDate)));
            $toDate = date('Y-m-d 23:59:59', strtotime(str_replace('/', '-', $req->toDate)));
        }

        $export = $req->export;
        if ($export == 0) {
            $search = $req->input('search.value');
            $limit = $req->length;
            $offset = $req->start;
            $srno = $_GET['start'] + 1;
            $draw = $_GET["draw"];
        }

        $deliveryType = $req->deliveryType;
        $orderfrom = $req->orderfrom;
        $orderno = $req->orderno;
        $storeLocation = $req->storeLocation;
        $tableName = "ordermaster";
        $orderColumns = array("ordermaster.*", "storelocation.storeLocation");
        $condition = array('ordermaster.orderFrom' => array('=', $orderfrom), 'ordermaster.deliveryType' => array('=', $deliveryType), 'ordermaster.code' => array('=', $orderno), 'ordermaster.storeLocation' => array('=', $storeLocation));
        $orderBy = array('ordermaster' . '.id' => 'DESC');
        $groupBy = array();
        $join = array('storelocation' => array('storelocation.code', 'ordermaster.storeLocation'));
        $joinType = array('storelocation' => 'left');
        $like = array('ordermaster.orderFrom' => $search, 'ordermaster.deliveryType' => $search, 'ordermaster.orderCode' => $search, 'ordermaster.storeLocation' => $search);


        $extraCondition = "ordermaster.created_at BETWEEN '$fromDate' AND '$toDate'";
        $result = $this->model->selectQueryWithGroupBy($orderColumns, $tableName, $join, $condition, $orderBy, $groupBy, $like, $limit, $offset, $extraCondition, $joinType);
        $r = DB::getQueryLog();

        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
                $role = '';
                $status = "";
                if ($export == 0) {
                    if ($row->orderFrom == "store") {
                        $status = '<span class="badge badge-success">Store</span>';
                    } else {
                        $status = '<span class="badge badge-primary">Online</span>';
                    }
                } else {
                    $status = ucfirst($row->orderFrom);
                }

                $data[] = array(
                    $srno,
                    $row->orderCode,
                    $row->deliveryType,
                    $status,
                    $row->grandTotal,
                );
                $srno++;
            }
            $dataCount = sizeof($this->model->selectQueryWithGroupBy($orderColumns, $tableName,  $join, $condition, $orderBy, $groupBy, $like, '', '', $extraCondition, $joinType));
        }
        $output = array(
            "draw" => intval($draw),
            "recordsTotal" => $dataCount,
            "recordsFiltered" => $dataCount,
            "data" => $data,
            "r" => $r
        );
        echo json_encode($output);
    }

    public function storeSummary()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['storelocation'] = DB::table('storelocation')->get();
            return view('reports.store-summary', $data);
        } else {
            return view('noright');
        }
    }

    public function storeSummaryList(Request $r)
    {

        $search = $limit = $offset = '';
        $srno = 1;

        $limit = "";
        $offset = $r->start;
        $srno = 1;
        $draw = 0;

        $export = $r->export;
        if ($export == 0) {
            $limit = $r->length;
            $offset = $r->start;
            $srno = $_GET['start'] + 1;
            $draw = $_GET["draw"];
        }


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

        $dataCount = $query->groupBy('sl.storeLocation')->count();
        if ($export == 0) {
            $ordersSummary = $query->groupBy('sl.storeLocation')->limit($limit)->offset($offset)->get();
        } else {
            $ordersSummary = $query->groupBy('sl.storeLocation')->get();
        }

        $dataCount = $ordersSummary->count();

        $data = [];
        foreach ($ordersSummary as $item) {
            $data[] = [
                $srno,
                $item->store_name,
                $item->total_orders,
                number_format($item->total_amount, 2),
                $item->online_orders,
                $item->in_store_orders
            ];
            $srno++;
        }

        $output = array(
            "draw" => intval($draw),
            "recordsTotal" => $dataCount,
            "recordsFiltered" => $dataCount,
            "data" => $data,
            "r" => $curDate
        );
        echo json_encode($output);
    }
}
