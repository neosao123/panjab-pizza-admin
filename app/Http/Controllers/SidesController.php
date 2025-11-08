<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use DB;



class SidesController extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('4.1', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    public function getSides(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('sidemaster.sidename' => $search);
        $condition = array('sidemaster.isDelete' => array('=', 0));
        $orderBy = array('sidemaster' . '.id' => 'DESC');
        $result = $this->model->selectQuery('sidemaster.*', 'sidemaster', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => ucwords(strtolower($item->sidename)));
            }
        }
        echo  json_encode($html);
    }

    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            return view('sides.list');
        } else {
            return view('noright');
        }
    }


    public function getSidesList(Request $req)
    {
        $sides = $req->sides;
        $type = $req->type;
        $search = $req->input('search.value');
        $tableName = "sidemaster";
        $orderColumns = array("sidemaster.*");
        $condition = array('sidemaster.isDelete' => array('=', 0), 'sidemaster.code' => array('=', $sides), 'sidemaster.type' => array('=', $type));
        $orderBy = array('sidemaster' . '.id' => 'DESC');
        $join = array();
        $like = array('sidemaster.type' => $search, 'sidemaster.sidename' => $search, 'sidemaster.code' => $search);
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
                $sidesImage = '';
                if ($row->image != '') {
                    $sidesImage = '<img src="' . url("uploads/sides/" . $row->image) . "?v=" . time() . '" height="50" width="50" alt="Sides Image">';
                }
  
                $actions = '<div class="btn-group">
                <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ti-settings"></i>
                </button>
                <div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">';
                if ($this->rights != '' && $this->rights['view'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("sides/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("sides/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights != '' && $this->rights['delete'] == 1) {
                    $actions .= '<a style="cursor:pointer;"class="dropdown-item delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash mr-2" href></i> Delete</a>';
                }

                $actions .= '</div>
                </div>';
                $data[] = array(
                    $srno,
                    $actions,
                    ucwords(strtolower($row->sidename)),
                    ucwords(strtolower($row->type)),
                    // $sidesImage,
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
            $sides = DB::table('sidemaster')->select('sidemaster.*')->where('sidemaster.code', $code)->first();
            if (!empty($sides)) {
                $data['sidelineentries'] = DB::table('sidelineentries')
                    ->select("sidelineentries.*")
                    ->where("sidelineentries.sidemasterCode", $code)
                    ->get();
                $data['queryresult'] = $sides;
                return view('sides.edit', $data);
            }
        } else {
            return view('noright');
        }
    }

    public function update(Request $r)
    {

        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'sideName' => 'required|min:3|max:150',
            'hasToppings' => 'nullable',
            'nooftoppings' => $r->hasToppings == 1 ? 'required|numeric|digits_between:1,2|gt:0' : "nullable",
        ];
        $messages = [
            'sideName.required' => 'Side name is required.',
            'sideName.max' => 'Maximum 150 characters are allowed.',
            'sideName.min' => 'Minimum 3 characters are required.',
            'nooftoppings.required' => 'The number of toppings is required when "hasToppings" is checked.',
            'nooftoppings.numeric' => 'The number of toppings must be a numeric value.',
            'nooftoppings.gt' => 'The number of toppings must be a greater than 0.',
            'nooftoppings.digits_between' => 'The number of toppings must have 1 to 99.',
        ];
        $this->validate($r, $rules, $messages);
        $data = [
            'sidename' => $r->sideName,
            'isActive' => $r->isActive == "" ? '0' : 1,
            'type' => $r->type,
            'hasToppings' => $r->hasToppings == 1 ? 1 : 0,
            'nooftoppings' =>  $r->nooftoppings ? $r->nooftoppings : 0,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code,
        ];
        $result = $this->model->doEdit($data, "sidemaster", $r->code);
        if ($filenew = $r->file('sidesImage')) {
            $imagename = $r->code . "." . $filenew->getClientOriginalExtension();
            $filenew->move('uploads/sides', $imagename);
            $image_data = ['image' => $imagename];
            $image_update = $this->model->doEdit($image_data, "sidemaster", $r->code);
        }
        if ($result == true) {
            if ($r->has('rowCode')) {
                $rowCodes = $r->rowCode;
                $size = $r->size;
                $price = $r->price;
                for ($i = 0; $i < count($rowCodes); $i++) {
                    $data = [
                        'size' => $size[$i],
                        'price' => $price[$i],
                        'isActive' => '1',
                        'isDelete' => '0'
                    ];
                    if (!empty($rowCodes[$i])) {
                        $data['editIP'] = $ip;
                        $data['editID'] = Auth::guard('admin')->user()->code;
                        $data['editDate'] = date('Y-m-d H:i:s');
                        $this->model->doEdit($data, "sidelineentries", $rowCodes[$i]);
                    }
                }
            }

            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Side " . $r->code . " is updated";
            $this->model->activity_log($data);
            //activity log end
            return redirect('/sides/list')->with('success', 'Sides updated successfully');
        }
        return back()->with('error', 'Failed to update the record');
    }

    public function deleteImage(Request $r)
    {
        $imgNm = $r->value;
        $code = $r->code;
        $data = array(
            'image' => '',
        );
        if (!empty($data)) {
            unlink('uploads/sides/' . $imgNm);
            echo $resultData = $this->model->doEdit($data, 'sidemaster', $code);
        } else {
            echo 'false';
        }
    }

    public function view(Request $request)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $sides = DB::table('sidemaster')->select('sidemaster.*')->where('sidemaster.code', $code)->first();
            if (!empty($sides)) {
                $data['sidelineentries'] = DB::table('sidelineentries')
                    ->select("sidelineentries.*")
                    ->where("sidelineentries.sidemasterCode", $code)
                    ->get();

                $data['queryresult'] = $sides;
                return view('sides.view', $data);
            }
        } else {
            return view('noright');
        }
    }

    // Add Side view
public function add()
{
    if ($this->rights != '' && $this->rights['insert'] == 1) {
        $insertRights = $this->rights['insert']; // Pass insert rights
        $viewRights = $this->rights['view'];
        return view('sides.add', compact('insertRights', 'viewRights'));
    } else {
        return view('noright');
    }
}
public function store(Request $r)
{
    $currentdate = Carbon::now();
    $ip = $_SERVER['REMOTE_ADDR'];
    $table = 'sidemaster';

    // Validation
    $validator = Validator::make($r->all(), [
        'sideName' => ['required','min:3','max:150'],
        'type' => 'required|in:side,subs,poutine,plantbites,tenders',
        'size.*' => [
            'required',
            'string',
            function ($attribute, $value, $fail) use ($r) {
                $sizes = $r->size ?? [];
                $normalized = array_map(fn($s) => Str::lower(trim($s)), $sizes);
                $counts = array_count_values($normalized);
                if ($counts[Str::lower(trim($value))] > 1) {
                    $fail("Each size must be unique, please enter a different size.");
                }
            }
        ],
        'price.*' => 'required|numeric|min:0',
    ], [
        'sideName.required' => 'Side name is required',
        'sideName.min' => 'Minimum of 3 characters are required.',
        'sideName.max' => 'Max characters exceeded.',
        'type.required' => 'Type is required',
        'type.in' => 'Invalid type selected',
        'price.*.required' => 'Price is required for each size',
        'price.*.numeric' => 'Price must be numeric',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Insert main side
    $data = [
        'sidename' => $r->sideName,
        'type' => $r->type,
        'isActive' => 1,
        'isDelete' => 0,
        'hasToppings' => $r->hasToppings == 1 ? 1 : 0,
        'nooftoppings' => $r->nooftoppings ?? 0,
        'addIP' => $ip,
        'addDate' => $currentdate->toDateTimeString(),
        'addID' => Auth::guard('admin')->user()->code
    ];

    // Handle image upload
    if ($r->hasFile('sidesImage')) {
        $file = $r->file('sidesImage');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/sides'), $filename);
        $data['image'] = $filename;
    }

    // Insert into database
    $code = $this->model->addNew($data, $table, 'SD'); // Prefix for Side

    if ($code) {
        // Insert toppings if provided
        if ($r->has('toppingName') && $r->has('toppingPrice')) {
            foreach ($r->toppingName as $i => $name) {
                $price = $r->toppingPrice[$i] ?? null;
                if (!empty($name) && !empty($price)) {
                    $this->model->addNew([
                        'sidemasterCode' => $code,
                        'toppingName' => $name,
                        'toppingPrice' => $price,
                        'isActive' => 1,
                        'isDelete' => 0,
                        'addIP' => $ip,
                        'addID' => Auth::guard('admin')->user()->code
                    ], 'sidetoppings', 'ST');
                }
            }
        }

        // Insert sizes & prices if provided
        if ($r->has('size') && $r->has('price')) {
            foreach ($r->size as $i => $size) {
                $price = $r->price[$i] ?? null;
                if (!empty($size) && !empty($price)) {
                    $this->model->addNew([
                        'sidemasterCode' => $code,
                        'size' => $size,
                        'price' => $price,
                        'isActive' => 1,
                        'isDelete' => 0,
                        'addIP' => $ip,
                        'addID' => Auth::guard('admin')->user()->code
                    ], 'sidelineentries', 'SLE');
                }
            }
        }

        // Activity log
        $log = $currentdate->toDateTimeString() . "\t" . $ip . "\t" . Auth::guard('admin')->user()->code . " Side " . $code . " is added.";
        $this->model->activity_log($log);

        return redirect('sides/list')->with('success', 'Side added successfully');
    }

    return back()->with('error', 'Failed to add Side');
}

// delete sides
public function delete(Request $r)
{
    $currentdate = Carbon::now();
    $code = $r->code;
    $ip = $_SERVER['REMOTE_ADDR'];
    $today = date('Y-m-d H:i:s');
    $table = 'sidemaster'; 

    $data = [
        'isActive'   => 0,
        'isDelete'   => 1,
        'deleteIP'   => $ip,
        'deleteID'   => Auth::guard('admin')->user()->code,
        'deleteDate' => $today
    ];

    $datastring = $currentdate->toDateTimeString() . "\t" . $ip . "\t" . Auth::guard('admin')->user()->code . " Sides " . $code . " is deleted.";
    $this->model->activity_log($datastring);

    $result = $this->model->doEditWithField($data, $table, 'code', $code);

    if ($result == true) {
        return response()->json(["status" => "success"], 200);
    } else {
        return response()->json(["status" => "fail"], 200);
    }
}
    
}
