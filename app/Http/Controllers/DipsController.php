<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\Dips;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;


class DipsController extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('3.9', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    public function getDips(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('dips.dips' => $search);
        $condition = array('dips.isDelete' => array('=', 0));
        $orderBy = array('dips' . '.id' => 'DESC');
        $result = $this->model->selectQuery('dips.*', 'dips', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => $item->dips);
            }
        }
        echo  json_encode($html);
    }

    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            return view('dips.list');
        } else {
            return view('noright');
        }
    }

    public function getDipsList(Request $req)
    {
        $dips = $req->dips;
        $search = $req->input('search.value');
        $tableName = "dips";
        $orderColumns = array("dips.*");
        $condition = array('dips.isDelete' => array('=', 0), 'dips.code' => array('=', $dips));
        $orderBy = array('dips' . '.id' => 'DESC');
        $join = array();
        $like = array('dips.dips' => $search, 'dips.code' => $search, 'dips.price' => $search);
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
                $dipsImage = '';
                if ($row->dipsImage != '') {
                    $dipsImage = '<img src="' . url("uploads/dips/" . $row->dipsImage) ."?v=".time() .'" height="50" width="50" alt="Dips Image">';
                }
				$actions = '<div class="btn-group">
                <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ti-settings"></i>
                </button>
                <div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">';
                if ($this->rights != '' && $this->rights['view'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("dips/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("dips/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights != '' && $this->rights['delete'] == 1) {
                    $actions .= '<a style="cursor:pointer;"class="dropdown-item delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash mr-2" href></i> Delete</a>';
                }

                $actions .= '</div>
                </div>';
                $data[] = array(
                    $srno,
					$actions,
                    $row->dips,
                    $row->price,
                    //$dipsImage,
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
            $dips = Dips::where('dips.code', $code)->first();
            if (!empty($dips)) {
                $data['queryresult'] = $dips;
                return view('dips.edit', $data);
            }
        } else {
            return view('noright');
        }
    }
	
	public function deleteImage(Request $r)
    {
        $imgNm = $r->value;
        $code = $r->code;
        $data = array(
            'dipsImage' => '',
        );
        if (!empty($data)) {
            unlink('uploads/dips/' . $imgNm);
            echo $resultData = $this->model->doEdit($data, 'dips', $code);
        } else {
            echo 'false';
        }
    }
	
	public function update(Request $r)
    {
        $table = "dips";
        $code = $r->code;
        $dips = $r->dips;
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'dips' => [
                'required',
                'min:3',
                'max:120',
                Rule::unique('dips')->where(function ($query)  use ($code, $dips) {
                    return $query->where('isDelete', '=', '0')
                        ->where('dips', '=',  $dips)
                        ->where('code', '!=', $code);
                })
            ],
            'dipsImage' => 'nullable|image|mimes:jpg,png,jpeg',
            'price' => 'nullable',
            'description' => 'nullable',
        ];
        $messages = [
            'dips.required' => 'Dips name is required',
            'dips.min' => 'Minimum of 3 characters are required.',
            'dips.max' => 'Max characters exceeded.'
        ];
        $this->validate($r, $rules, $messages);
        $data = [
            'dips' => ucwords(strtolower($r->dips)),
            'price' => $r->price,
            'description' => $r->description,
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code
        ];
        $result = $this->model->doEdit($data, $table, $code);
        if ($filenew = $r->file('dipsImage')) {
            $imagename = $code . "." . $filenew->getClientOriginalExtension();
            $filenew->move('uploads/dips', $imagename);
            $image_data = ['dipsImage' => $imagename];
            $image_update = $this->model->doEdit($image_data, $table, $code);
        }
        if ($result == true || $image_update == true) {
            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Dips " . $code . " is updated.";
            $this->model->activity_log($data);
            //activity log end
            return redirect('dips/list')->with('success', 'Dips updated successfully');
        } else {
            return back()->with('error', 'Failed to update the dips');
        }
    }
	
	 public function view(Request $request)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $dips = Dips::where('dips.code', $code)->first();
            if (!empty($dips)) {
                $data['queryresult'] = $dips;
                return view('dips.view', $data);
            }
        } else {
            return view('noright');
        }
    }
    // Add Dips view
public function add()
{
    if ($this->rights != '' && $this->rights['insert'] == 1) {
        $insertRights = $this->rights['insert']; // Pass insert rights
        $viewRights = $this->rights['view'];
        return view('dips.add', compact('insertRights', 'viewRights'));
    } else {
        return view('noright');
    }
}

// Store new Dips
public function store(Request $r)
{
    $table = "dips"; // Table name
    $dipsName = $r->dips;
    $currentdate = Carbon::now();
    $ip = $_SERVER['REMOTE_ADDR'];

    // Validation rules
    $rules = [
        'dips' => [
            'required',
            'min:3',
            'max:120',
            Rule::unique('dips', 'dips')->where(function ($query) use ($dipsName) {
                return $query->where('isDelete', 0)
                             ->where('dips', $dipsName);
            })
        ],
        'price' => 'required|numeric|min:0.01',
        'description' => 'nullable',
    ];

    $messages = [
        'dips.required' => 'Dips Name is required',
        'dips.min' => 'Minimum of 3 characters are required.',
        'dips.max' => 'Max characters exceeded.',
        'price.required' => 'Price is required',
        'price.numeric' => 'Price must be a number'
    ];

    $this->validate($r, $rules, $messages);

    // Prepare data
    $data = [
        'dips' => $r->dips,
        'price' => $r->price,
        'description' => $r->description,
        'isActive' => 1, // default active
        'isDelete' => 0,
        'addIP' => $ip,
        'addDate' => $currentdate->toDateTimeString(),
        'addID' => Auth::guard('admin')->user()->code
    ];

    // Handle image upload
    if ($r->hasFile('dipsImage')) {
        $file = $r->file('dipsImage');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/dips'), $filename);
        $data['dipsImage'] = $filename;
    }

    // Add new dips
    $code = $this->model->addNew($data, $table, 'DIP'); // Prefix for dips

    if ($code) {
        $log = $currentdate->toDateTimeString() . "\t" . $ip . "\t" . Auth::guard('admin')->user()->code . " Dips " . $code . " is added.";
        $this->model->activity_log($log);

        return redirect('dips/list')->with('success', 'Dips added successfully');
    } else {
        return back()->with('error', 'Failed to add Dips');
    }
}
// delete dips
public function delete(Request $r)
{
    $currentdate = Carbon::now();
    $code = $r->code;
    $ip = $_SERVER['REMOTE_ADDR'];
    $today = date('Y-m-d H:i:s');
    $table = 'dips'; 

    $data = [
        'isActive'   => 0,
        'isDelete'   => 1,
        'deleteIP'   => $ip,
        'deleteID'   => Auth::guard('admin')->user()->code,
        'deleteDate' => $today
    ];

    $datastring = $currentdate->toDateTimeString() . "\t" . $ip . "\t" . Auth::guard('admin')->user()->code . " dip " . $code . " is deleted.";
    $this->model->activity_log($datastring);

    $result = $this->model->doEditWithField($data, $table, 'code', $code);

    if ($result == true) {
        return response()->json(["status" => "success"], 200);
    } else {
        return response()->json(["status" => "fail"], 200);
    }
}

}
