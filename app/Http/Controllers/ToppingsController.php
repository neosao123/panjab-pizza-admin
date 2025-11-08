<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;


class ToppingsController extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('3.10', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    public function getToppings(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('toppings.toppings' => $search);
        $condition = array('toppings.isDelete' => array('=', 0));
        $orderBy = array('toppings' . '.id' => 'DESC');
        $result = $this->model->selectQuery('toppings.*', 'toppings', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => ucwords(strtolower($item->toppingsName)));
            }
        }
        echo  json_encode($html);
    }

    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            return view('toppings.list');
        } else {
            return view('noright');
        }
    }


    public function getToppingsList(Request $req)
    {
        $toppings = $req->toppings;
        $countas = $req->countas;
        $search = $req->input('search.value');
        $tableName = "toppings";
        $orderColumns = array("toppings.*");
        if (is_numeric($countas)) {
            $condition = array('toppings.isDelete' => array('=', 0), 'toppings.code' => array('=', $toppings), 'toppings.countAs' => array('=', $countas));
        } else if (is_string($countas)) {
            $condition = array('toppings.isDelete' => array('=', 0), 'toppings.code' => array('=', $toppings), 'toppings.isPaid' => array('=', 0));
        } else {
            $condition = array('toppings.isDelete' => array('=', 0), 'toppings.code' => array('=', $toppings));
        }
        $orderBy = array('toppings' . '.id' => 'DESC');
        $join = array();
        $like = array('toppings.toppingsName' => $search, 'toppings.code' => $search, 'toppings.price' => $search, 'toppings.countAs' => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
        $srno = $_GET['start'] + 1;
        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
                $role = '';
                $status = '<span class="badge badge-danger"> InActive </span>';
                if ($row->isActive == 1) {
                    $status = '<span class="badge badge-success">Active</span>';
                }
                $type = '<span class="badge badge-danger">Free </span>';
                if ($row->isPaid == 1) {
                    $type = '<span class="badge badge-success">Paid</span>';
                }
                $toppingsImage = '';
                if ($row->toppingsImage != '') {
                    $toppingsImage = '<img src="' . url("uploads/toppings/" . $row->toppingsImage) . "?v=" . time() . '" height="50" width="50" alt="Toppings Image">';
                }
                $actions = '<div class="btn-group">
                <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ti-settings"></i>
                </button>
                <div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">';
                if ($this->rights != '' && $this->rights['view'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("toppings/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("toppings/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights != '' && $this->rights['delete'] == 1) {
                    $actions .= '<a style="cursor:pointer;"class="dropdown-item delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash mr-2" href></i> Delete</a>';
                }
                             
                $actions .= '</div></div>';
                $data[] = array(
                    $srno,
                    $actions,
                    ucwords(strtolower($row->toppingsName)),
                    ucwords($row->topping_type),
                    $row->price,
                    //$toppingsImage,
                    $type,
                    $status,
                );
                $srno++;
            }
            $dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', ''));
        }
        $output = array(
            "draw" => intval($_GET["draw"]),
            "recordsTotal" => $dataCount,
            "recordsFiltered" => $dataCount,
            "data" => $data
        );
        echo json_encode($output);
    }

    public function edit(Request $request)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $data['setting'] = Setting::where('id', 7)->first();
            $data['settingNonRegular'] = Setting::where('id', 5)->first();
            $data['settingRegular'] = Setting::where('id', 6)->first();
            $toppings = DB::table('toppings')->select('toppings.*')->where('toppings.code', $code)->first();
            if (!empty($toppings)) {
                $data['queryresult'] = $toppings;
                return view('toppings.edit', $data);
            }
        } else {
            return view('noright');
        }
    }

    public function add()
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $data['setting'] = Setting::where('id', 7)->first();
            $data['settingNonRegular'] = Setting::where('id', 5)->first();
            $data['settingRegular'] = Setting::where('id', 6)->first();
            return view('toppings.add', $data);
        } else {
            return view('noright');
        }
    }

    public function store(Request $r)
    {
        $table = 'toppings';
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];

        $result = $this->model->selectQuery('toppings.*', 'toppings', array(), array(), '', '', '', '');

        if ($result) {
            $condition = $result->contains(function ($item) use ($r) {
                return $item->toppingsName == ucwords(strtolower($r->toppingsName));
            });
            if ($condition != 1) {
                $data = [
                    'toppingsName' => ucwords(strtolower($r->toppingsName)),
                    'countAs' => $r->countAs,
                    'topping_type' => $r->topping_type,
                    'isPaid' => $r->isPaid,
                    'price' => $r->price,
                    'isActive' => $r->isActive == "" ? '0' : 1,
                    'isDelete' => 0,
                    'addIP' => $ip,
                    'addDate' => $currentdate->toDateTimeString(),
                ];
                $res = $this->model->addNew($data, $table, 'TOP');
                if ($res) {
                    return redirect('toppings/list')->with('success', 'Record added successfully', $res);
                }
                return back()->with('error', 'Failed to add the record');
            } else {
                return back()->with('error', 'Failed to add the record, Toppings name already exist');
            }
        }
    }

    public function deleteImage(Request $r)
    {
        $imgNm = $r->value;
        $code = $r->code;
        $data = array(
            'toppingsImage' => '',
        );
        if (!empty($data)) {
            unlink('uploads/toppings/' . $imgNm);
            echo $resultData = $this->model->doEdit($data, 'toppings', $code);
        } else {
            echo 'false';
        }
    }

    public function update(Request $r)
    {
        $table = "toppings";
        $code = $r->code;
        $toppingsName = $r->toppingsName;
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'toppingsName' => [
                'required',
                'min:3',
                'max:120',
                Rule::unique('toppings')->where(function ($query)  use ($code, $toppingsName) {
                    return $query->where('isDelete', '=', '0')
                        ->where('toppingsName', '=',  $toppingsName)
                        ->where('code', '!=', $code);
                })
            ],
            'countAs' => 'nullable',
            'toppingsImage' => 'nullable|image|mimes:jpg,png,jpeg',
            'price' => 'nullable',
        ];
        $messages = [
            'toppingsName.required' => 'Toppings name is required',
            'toppingsName.min' => 'Minimum of 3 characters are required.',
            'toppingsName.max' => 'Max characters exceeded.',
        ];
        $this->validate($r, $rules, $messages);

        $data = [
            'toppingsName' => ucwords(strtolower($r->toppingsName)),
            'countAs' => $r->countAs,
            'topping_type' => $r->topping_type,
            'isPaid' => $r->isPaid,
            'price' => $r->price,
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code
        ];
        $result = $this->model->doEdit($data, $table, $code);
        if ($filenew = $r->file('toppingsImage')) {
            $imagename = $code . "." . $filenew->getClientOriginalExtension();
            $filenew->move('uploads/toppings', $imagename);
            $image_data = ['toppingsImage' => $imagename];
            $image_update = $this->model->doEdit($image_data, $table, $code);
        }
        if ($result == true || $image_update == true) {
            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Topping " . $code . " is updated.";
            $this->model->activity_log($data);
            //activity log end
            return redirect('toppings/list')->with('success', 'Topping updated successfully');
        } else {
            return back()->with('error', 'Failed to update the topping');
        }
    }

    public function view(Request $request)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $data['setting'] = Setting::where('id', 7)->first();
            $data['settingNonRegular'] = Setting::where('id', 5)->first();
            $data['settingRegular'] = Setting::where('id', 6)->first();
            $toppings = DB::table('toppings')->select('toppings.*')->where('toppings.code', $code)->first();
            if (!empty($toppings)) {
                $data['queryresult'] = $toppings;
                return view('toppings.view', $data);
            }
        } else {
            return view('noright');
        }
    }
    // delete  toppings
public function delete(Request $r)
{
    $currentdate = Carbon::now();
    $code = $r->code;
    $ip = $_SERVER['REMOTE_ADDR'];
    $today = date('Y-m-d H:i:s');
    $table = 'toppings'; 

    $data = [
        'isActive'   => 0,
        'isDelete'   => 1,
        'deleteIP'   => $ip,
        'deleteID'   => Auth::guard('admin')->user()->code,
        'deleteDate' => $today
    ];

    $datastring = $currentdate->toDateTimeString() . "\t" . $ip . "\t" . Auth::guard('admin')->user()->code . " topping " . $code . " is deleted.";
    $this->model->activity_log($datastring);

    $result = $this->model->doEditWithField($data, $table, 'code', $code);

    if ($result == true) {
        return response()->json(["status" => "success"], 200);
    } else {
        return response()->json(["status" => "fail"], 200);
    }
}

}
