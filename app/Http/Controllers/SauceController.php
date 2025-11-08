<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\Sauce;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;

class SauceController extends Controller
{
    private $role, $rights;

    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('3.6', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    public function getSauce(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('sauce.sauce' => $search);
        $condition = array('sauce.isDelete' => array('=', 0));
        $orderBy = array('sauce' . '.id' => 'DESC');
        $result = $this->model->selectQuery('sauce.*', 'sauce', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => $item->sauce);
            }
        }
        echo  json_encode($html);
    }

    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['insertRights'] = $this->rights['insert'];
            return view('sauce.list', $data);
        } else {
            return view('noright');
        }
    }

    public function getSauceList(Request $req)
    {
        $sauce = $req->sauce;
        $search = $req->input('search.value');
        $tableName = "sauce";
        $orderColumns = array("sauce.*");
        $condition = array('sauce.isDelete' => array('=', 0), 'sauce.code' => array('=', $sauce));
        $orderBy = array('sauce' . '.id' => 'DESC');
        $join = array();
        $like = array('sauce.sauce' => $search, 'sauce.code' => $search);
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
                    $actions .= '<a class="dropdown-item" href="' . url("sauce/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("sauce/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights != '' && $this->rights['delete'] == 1) {
                    $actions .= '<a style="cursor:pointer;"class="dropdown-item delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash mr-2" href></i> Delete</a>';
                }

                $actions .= '</div>
               </div>';
                $data[] = array(
                    $srno,
                    $actions,
                    $row->sauce,
                    // $row->price,
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

    public function view(Request $request)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $sauce = Sauce::where('sauce.code', $code)->first();
            if (!empty($sauce)) {
                $data['queryresult'] = $sauce;
                return view('sauce.view', $data);
            }
        } else {
            return view('noright');
        }
    }

    public function edit(Request $request)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;

            $sauce = Sauce::where('sauce.code', $code)->first();
            if (!empty($sauce)) {
                $data['queryresult'] = $sauce;
                return view('sauce.edit', $data);
            }
        } else {
            return view('noright');
        }
    }

    public function update(Request $r)
    {
        $table = "sauce";
        $code = $r->code;
        $sauce = $r->sauce;
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'sauce' => [
                'required',
                'min:3',
                'max:120',
                Rule::unique('sauce')->where(function ($query)  use ($code, $sauce) {
                    return $query->where('isDelete', '=', '0')
                        ->where('sauce', '=',  $sauce)
                        ->where('code', '!=', $code);
                })
            ],
        ];
        $messages = [
            'sauce.required' => 'Name is required',
            'sauce.min' => 'Minimum of 3 characters are required.',
            'sauce.max' => 'Max characters exceeded.',
        ];
        $this->validate($r, $rules, $messages);
        $data = [
            'sauce' => ucwords(strtolower($r->sauce)),
            // 'price' => $r->price,
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code
        ];
        $result = $this->model->doEdit($data, $table, $code);
        if ($result == true) {
            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Sauce " . $code . " is updated.";
            $this->model->activity_log($data);
            //activity log end
            return redirect('sauce/list')->with('success', 'Sauce updated successfully');
        } else {
            return back()->with('error', 'Failed to update the sauce');
        }
    }
    // Add Sauce view
public function add()
{
    if ($this->rights != '' && $this->rights['insert'] == 1) {
        $insertRights = $this->rights['insert']; // Pass insert rights
        $viewRights = $this->rights['view'];
        return view('sauce.add', compact('insertRights', 'viewRights'));
    } else {
        return view('noright');
    }
}


// Store new Sauce
public function store(Request $r)
{
    $table = "sauce"; // Table name for sauces
    $sauceName = $r->sauce;
    $currentdate = Carbon::now();
    $ip = $_SERVER['REMOTE_ADDR'];

    $rules = [
        'sauce' => [
            'required',
            'min:3',
            'max:120',
            Rule::unique($table, 'sauce')->where(function ($query) use ($sauceName) {
                return $query->where('isDelete', '=', '0')
                             ->where('sauce', '=', $sauceName);
            })
        ],
    ];

    $messages = [
        'sauce.required' => 'Sauce Name is required',
        'sauce.min' => 'Minimum of 3 characters are required.',
        'sauce.max' => 'Max characters exceeded.',
    ];

    $this->validate($r, $rules, $messages);

    $data = [
        'sauce' => ucwords(strtolower($r->sauce)), 
        'isActive' => $r->has('isActive') ? $r->isActive : 1,
        'isDelete' => 0,
        'addIP' => $ip,
        'addDate' => $currentdate->toDateTimeString(),
        'addID' => Auth::guard('admin')->user()->code
    ];

    $code = $this->model->addNew($data, $table, 'SA'); // SCE = prefix for Sauce

    if ($code) {
        $log = $currentdate->toDateTimeString() . "\t" . $ip . "\t" . Auth::guard('admin')->user()->code . " Sauce " . $code . " is added.";
        $this->model->activity_log($log);

        return redirect('sauce/list')->with('success', 'Sauce added successfully');
    } else {
        return back()->with('error', 'Failed to add sauce');
    }
}
// delete sauce
public function delete(Request $r)
{
    $currentdate = Carbon::now();
    $code = $r->code;
    $ip = $_SERVER['REMOTE_ADDR'];
    $today = date('Y-m-d H:i:s');
    $table = 'sauce'; // Correct table name

    $data = [
        'isActive'   => 0,
        'isDelete'   => 1,
        'deleteIP'   => $ip,
        'deleteID'   => Auth::guard('admin')->user()->code,
        'deleteDate' => $today
    ];

    $datastring = $currentdate->toDateTimeString() . "\t" . $ip . "\t" . Auth::guard('admin')->user()->code . " Sauce " . $code . " is deleted.";
    $this->model->activity_log($datastring);

    $result = $this->model->doEditWithField($data, $table, 'code', $code);

    if ($result == true) {
        return response()->json(["status" => "success"], 200);
    } else {
        return response()->json(["status" => "fail"], 200);
    }
}


}
