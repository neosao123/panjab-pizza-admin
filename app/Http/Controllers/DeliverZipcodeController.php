<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Storelocation;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\Dips;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class DeliverZipcodeController extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('2.4', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $data['storelocation'] = DB::table('storelocation')->get();
        return view('zipcode.index', $data);
    }

    public function getZipcodeList(Request $req)
    {
        $search = $req->input('search.value');
        $tableName = "zipcode";
        $orderColumns = array("zipcode.*","storelocation.storeLocation");
        $condition = array('zipcode.isDelete' => array('=', 0));
        $orderBy = array('zipcode' . '.id' => 'DESC');
        $groupBy = array();
        $join = array('storelocation' => array('storelocation.code', 'zipcode.storeCode'));
        $joinType = array('storelocation' => 'left');
        $like = array('zipcode.zipcode' => $search, 'zipcode.code' => $search,'storelocation.storeLocation'=>$search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQueryWithGroupBy($orderColumns, $tableName, $join, $condition, $orderBy, $groupBy, $like, $limit, $offset, $extraCondition, $joinType);
        $srno = $_GET['start'] + 1;
        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
                /*$store_location_data = DB::table('storelocation')->where('code', $row->storeCode)->first();
                if ($store_location_data) {
                    $store_location = $store_location_data->storeLocation;
                } else {
                    $store_location = 'Location not found'; // Handle when no matching store location is found
                }*/

                if ($row->isActive == 1) {
                    $status = '<span class="badge badge-success">Active</span>';
                } else {
                    $status = '<span class="badge badge-danger"> InActive </span>';
                }
                $actions = '<div class="text-center"> 
						<a class="btn btn-outline-info btn-sm icons_padding edit" data-id="' . $row->code . '" title="Edit"><i class="fas fa-edit"  style="font-size:18px;"></i></a>
						<a class="delete_id btn btn-outline-danger btn-sm icons_padding" id="' . $row->code . '" title="Delete"><i class="fa fa-trash" style="font-size:18px;"></i></a>
					</div>';
                $data[] = array(
                    $srno,
                    $row->zipcode,
                    $row->storeLocation,
                    $status,
                    $actions
                );
                $srno++;
            }
            $dataCount = sizeof($this->model->selectQueryWithGroupBy($orderColumns, $tableName,  $join, $condition, $orderBy, $groupBy, $like, '', '', $extraCondition, $joinType));
        }
        $output = array(
            "draw" => intval($_GET["draw"]),
            "recordsTotal" => $dataCount,
            "recordsFiltered" => $dataCount,
            "data" => $data
        );

        echo json_encode($output);
    }

    public function edit(Request $r)
    {
        $code = $r->code;
        $getDetails = DB::table("zipcode")->select("zipcode.*")->where("zipcode.code", $code)->first();
        if ($getDetails) {
            $data['code'] = $getDetails->code;
            $data['zipcode'] = $getDetails->zipcode;
            $data['storeLocation'] = $getDetails->storeCode;
            $data['isActive'] = $getDetails->isActive;
            return response()->json(["status" => 200, "msg" => "Data found", "data" => $data], 200);
        }
        return response()->json(["msg" => "Data Not Found"], 400);
    }

    public function store(Request $r)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $table = "zipcode";
        $rules = array(
            'id' => 'nullable',
            'zipcode' => [
                'required',
                'regex:/^[ABCEGHJKLMNPRSTVXY]\d[A-Z]\d[A-Z]\d$/i',
            ],
            'storeLocation' => 'required',
        );
        $messages = array(
            'zipcode.required' => 'The postal code is required',
            'storeLocation.required' => 'Store Location is Required',
        );
        $validator = Validator::make($r->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        } else {
            $zipcode =  ucwords($r->zipcode);
            $storeCode = ucwords($r->storeLocation);
            if ($r->has('code') && trim($r->code) != "") {
                $code = $r->code;
                $where[] = ["zipcode.zipcode", "=", $zipcode];
                $where[] = ["zipcode.code", "!=", $code];
                $duplicate = $this->model->checkForDuplicate($table, "zipcode", $where);
                if (!$duplicate) {
                    $data = [
                        'zipcode' => $zipcode,
                        'storeCode' => $storeCode,
                        'isActive' => $r->isActive == "" ? '0' : 1,
                        'isDelete' => 0,
                        'editIP' => $ip,
                        'editDate' => $currentdate->toDateTimeString(),
                        'editID' => Auth::guard('admin')->user()->code,
                    ];
                    $result = $this->model->doEdit($data, $table, $r->code);
                    if ($result != false) {
                        //activity log start
                        $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Zipcode " . $code . " is updated.";
                        $this->model->activity_log($data);
                        //activity log end

                        $res['status'] = 200;
                        $res['msg'] = "The postal code updated successfully ";
                    } else {
                        $res['status'] = 300;
                        $res['msg'] = "No changes were made";
                    }
                } else {
                    $res['status'] = 400;
                    $res['msg'] = "Duplicate records are not allowed";
                }
            } else {
                $where[] = ["zipcode.zipcode", "=", $zipcode];
                $duplicate = $this->model->checkForDuplicate($table, "zipcode", $where);
                if (!$duplicate) {
                    $data = [
                        'zipcode' => $zipcode,
                        'storeCode' => $storeCode,
                        'isActive' => $r->isActive == "" ? '0' : 1,
                        'isDelete' => 0,
                        'addIP' => $ip,
                        'addDate' => $currentdate->toDateTimeString(),
                        'addID' => Auth::guard('admin')->user()->code,
                    ];
                    $result = $this->model->addNew($data, 'zipcode', 'ZIP');
                    if ($result) {

                        //activity log start
                        $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Zipcode " . $result . " is added.";
                        $this->model->activity_log($data);
                        //activity log end

                        $res['status'] = 200;
                        $res['msg'] = "Zipcode saved successfully";
                    } else {
                        $res['status'] = 300;
                        $res['msg'] = "Failed to add zipcode";
                    }
                } else {
                    $res['status'] = 400;
                    $res['msg'] = "Duplicate records are not allowed";
                }
            }
            return response()->json($res, 200);
        }
    }

    public function delete(Request $request)
    {
        $code = $request->code;
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $today = date('Y-m-d');
        $table = 'zipcode';
        $data = ['isActive' => 0, 'isDelete' => 1, 'deleteIP' => $ip, 'deleteID' => Auth::guard('admin')->user()->code, 'deleteDate' => $currentdate];

        //activity log start
        $data1 = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Zipcode " . $code . " is deleted.";
        $this->model->activity_log($data1);
        //activity log end
        echo $this->model->doEditWithField($data, 'zipcode', 'code', $code);
    }

    public function getStoreLocation(Request $request)
    {
        $html = [];
        $search  = $request->input('search');

        $storeLocation = Storelocation::where('isActive', 1)
            ->where('storeLocation', 'like', '%' . $search . '%')
            ->where('isDelete', 0)
            ->limit(10)->get();

        if ($storeLocation) {
            foreach ($storeLocation as $store) {
                $html[] = ['id' => $store->code, 'text' => $store->storeLocation];
            }
        }
        return response()->json($html);
    }

    public function importZipcodes()
    {
        return view('zipcode.import');
    }

    public function uploadZipcodes(Request $request)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $data = $request->all();
        $duplicatePostalCodes = $this->checkDuplicatePostalCode($data);

        if ($duplicatePostalCodes->isEmpty()) {
            foreach ($data as $row) {
                $data = [
                    'zipcode' => $row['postalcode'],
                    'storeCode' => $row['storeLocation'],
                    'isActive' => 1,
                    'isDelete' => 0,
                    'addIP' => $ip,
                    'addDate' => $currentdate->toDateTimeString(),
                    'addID' => Auth::guard('admin')->user()->code,
                ];
                $result = $this->model->addNew($data, 'zipcode', 'ZIP');
                if ($result) {
                    //activity log start
                    $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Zipcode " . $result . " is added.";
                    $this->model->activity_log($data);
                    //activity log end

                    $res['status'] = 200;
                    $res['msg'] = "The postal code added successfully";
                } else {
                    $res['status'] = 300;
                    $res['msg'] = "Failed to add postal code";
                }
            }
            return response()->json($res, 200);
        } else {
            $res['status'] = 300;
            $res['msg'] = "Duplicate postal codes were found in the uploaded data. Please ensure that each postal code is unique.";
            return response()->json($res, 200);
        }
    }

    public function checkDuplicatePostalCode($data)
    {
        $postalCodes = array_column($data, 'postalcode');
        return DB::table('zipcode')->whereIn('zipcode', $postalCodes)->where('isDelete', 0)->get();
    }
}
