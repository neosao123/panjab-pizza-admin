<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\Specialbases;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;

class SpecialbasesController extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('3.4', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    public function getSpecialBases(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('specialbases.specialbase' => $search);
        $condition = array('specialbases.isDelete' => array('=', 0));
        $orderBy = array('specialbases' . '.id' => 'DESC');
        $result = $this->model->selectQuery('specialbases.*', 'specialbases', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => $item->specialbase);
            }
        }
        echo  json_encode($html);
    }

    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            return view('specialbases.list');
        } else {
            return view('noright');
        }
    }

    public function getSpecialBasesList(Request $req)
    {
        $specialbase = $req->specialbase;
        $search = $req->input('search.value');
        $tableName = "specialbases";
        $orderColumns = array("specialbases.*");
        $condition = array('specialbases.isDelete' => array('=', 0), 'specialbases.code' => array('=', $specialbase));
        $orderBy = array('specialbases' . '.specialbase' => 'ASC');
        $join = array();
        $like = array('specialbases.specialbase' => $search, 'specialbases.code' => $search, 'specialbases.price' => $search);
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
				$actions = '<div class="btn-group">
                <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ti-settings"></i>
                </button>
                <div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">';
                if ($this->rights != '' && $this->rights['view'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("specialbases/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("specialbases/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights != '' && $this->rights['delete'] == 1) {
                    $actions .= '<a style="cursor:pointer;" class="dropdown-item delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash mr-2"></i> Delete</a>';
                }

                $actions .= '</div>
               </div>';
                $data[] = array(
                    $srno,
					$actions,
                    $row->specialbase,
                    $row->price,
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
            $specialbases = Specialbases::where('specialbases.code', $code)->first();
            if (!empty($specialbases)) {
                $data['queryresult'] = $specialbases;
                return view('specialbases.edit', $data);
            }
        } else {
            return view('noright');
        }
    }
	
	public function view(Request $request)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $specialbases = Specialbases::where('specialbases.code', $code)->first();
            if (!empty($specialbases)) {
                $data['queryresult'] = $specialbases;
                return view('specialbases.view', $data);
            }
        } else {
            return view('noright');
        }
    }
	
	
	public function update(Request $r)
    {
        $table = "specialbases";
        $code = $r->code;
        $specialbase = $r->specialbase;
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'specialbase' => [
                'required',
                'min:3',
                'max:120',
                Rule::unique('specialbases')->where(function ($query)  use ($code, $specialbase) {
                    return $query->where('isDelete', '=', '0')
                        ->where('specialbase', '=',  $specialbase)
                        ->where('code', '!=', $code);
                })
            ],
			'price'=>'required',
        ];
        $messages = [
            'specialbase.required' => 'Special Base Name is required',
            'specialbase.min' => 'Minimum of 3 characters are required.',
            'specialbase.max' => 'Max characters exceeded.',
			'price.required' => 'Price is required.',
        ];
        $this->validate($r, $rules, $messages);
        $data = [
            'specialbase' => ucwords(strtolower($r->specialbase)),
			'price'=>$r->price,
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code
        ];
        $result = $this->model->doEdit($data, $table, $code);
        if ($result == true) {
            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Special Base  " . $code . " is updated.";
            $this->model->activity_log($data);
            //activity log end
            return redirect('specialbases/list')->with('success', 'Special Base updated successfully');
        } else {
            return back()->with('error', 'Failed to update the special base');
        }
    }


    // Add special base view
public function add()
{
    if ($this->rights != '' && $this->rights['insert'] == 1) {
        $viewRights = $this->rights['view'];
        return view('specialbases.add', compact('viewRights'));
    } else {
        return view('noright');
    }
}
public function store(Request $r)
{
    $table = "specialbases";  
    $specialbase = $r->specialbase;  // matches form input name
    $currentdate = Carbon::now();
    $ip = $_SERVER['REMOTE_ADDR'];

    $rules = [
        'specialbase' => [
            'required',
            'min:3',
            'max:120',
            Rule::unique($table, 'specialbase')->where(function ($query) use ($specialbase) {
                return $query->where('isDelete', '=', '0')
                             ->where('specialbase', '=', $specialbase);
            })
        ],
        'price' => 'required|numeric|min:0',
    ];

    $messages = [
        'specialbase.required' => 'Special Base Name is required',
        'specialbase.min' => 'Minimum of 3 characters are required.',
        'specialbase.max' => 'Max characters exceeded.',
        'price.required' => 'Price is required.',
        'price.numeric' => 'Price must be a number.',
        'price.min' => 'Price must be 0 or more.',
    ];

    $this->validate($r, $rules, $messages);

    $data = [
        'specialbase' => ucwords(strtolower($r->specialbase)),
        'price' => $r->price,
        'isActive' => $r->has('isActive') ? $r->isActive : 1,
        'isDelete' => 0,
        'addIP' => $ip,
        'addDate' => $currentdate->toDateTimeString(),
        'addID' => Auth::guard('admin')->user()->code
    ];

    $code = $this->model->addNew($data, $table, 'SPB'); 

    if ($code) {
        $log = $currentdate->toDateTimeString() . "\t" . $ip . "\t" . Auth::guard('admin')->user()->code . " Special Base " . $code . " is added.";
        $this->model->activity_log($log);

        return redirect('specialbases/list')->with('success', 'Special Base added successfully');
    } else {
        return back()->with('error', 'Failed to add special base');
    }
}

// delete special base
public function delete(Request $r)
{
    $currentdate = Carbon::now();
    $code = $r->code;
    $ip = $_SERVER['REMOTE_ADDR'];
    $today = date('Y-m-d H:i:s');
    $table = 'specialbases'; // Correct table name
    
    $data = [
        'isActive'   => 0,
        'isDelete'   => 1,
        'deleteIP'   => $ip,
        'deleteID'   => Auth::guard('admin')->user()->code,
        'deleteDate' => $today
    ];

    $datastring = $currentdate->toDateTimeString() . "	" . $ip . "	" . Auth::guard('admin')->user()->code . "	Special Base " . $code . " is deleted.";
    $this->model->activity_log($datastring);

    $result = $this->model->doEditWithField($data, $table, 'code', $code);

    if ($result == true) {
        return response()->json(["status" => "success"], 200);
    } else {
        return response()->json(["status" => "fail"], 200);
    }
}
}