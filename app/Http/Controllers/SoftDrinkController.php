<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\Softdrinks;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;


class SoftDrinkController extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('3.8', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    public function getSoftDrink(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('softdrinks.softdrinks' => $search);
        $condition = array('softdrinks.isDelete' => array('=', 0));
        $orderBy = array('softdrinks' . '.id' => 'DESC');
        $result = $this->model->selectQuery('softdrinks.*', 'softdrinks', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => $item->softdrinks);
            }
        }
        echo  json_encode($html);
    }

    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            return view('softdrinks.list');
        } else {
            return view('noright');
        }
    }

    public function getSoftDrinkList(Request $req)
    {
        $softdrink = $req->softdrink;
        $search = $req->input('search.value');
        $tableName = "softdrinks";
        $orderColumns = array("softdrinks.*");
        $condition = array('softdrinks.isDelete' => array('=', 0), 'softdrinks.code' => array('=', $softdrink));
        $orderBy = array('softdrinks' . '.id' => 'DESC');
        $join = array();
        $like = array('softdrinks.type' => $search, 'softdrinks.softdrinks' => $search, 'softdrinks.code' => $search, 'softdrinks.price' => $search);
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
                $softdrinksImage = '';
                if ($row->softDrinkImage != '') {
                    $softdrinksImage = '<img src="' . url("uploads/softdrinks/" . $row->softDrinkImage) . "?v=" . time() . '" height="50" width="50" alt="Soft Drink Image">';
                }
                $actions = '<div class="btn-group">
                <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ti-settings"></i>
                </button>
                <div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">';
                if ($this->rights != '' && $this->rights['view'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("softdrinks/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("softdrinks/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights != '' && $this->rights['delete'] == 1) {
                    $actions .= '<a style="cursor:pointer;"class="dropdown-item delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash mr-2" href></i> Delete</a>';
                }

                $actions .= '</div>
            </div>';
                $data[] = array(
                    $srno,
                    $actions,
                    $row->softdrinks,
                    $row->price,
                    ucfirst($row->type),
                    //$softdrinksImage,
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
            $softdrinks = Softdrinks::where('softdrinks.code', $code)->first();
            if (!empty($softdrinks)) {
                $data['queryresult'] = $softdrinks;
                return view('softdrinks.edit', $data);
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
            'softDrinkImage' => '',
        );
        if (!empty($data)) {
            unlink('uploads/softdrinks/' . $imgNm);
            echo $resultData = $this->model->doEdit($data, 'softdrinks', $code);
        } else {
            echo 'false';
        }
    }

    public function update(Request $r)
    {
        $table = "softdrinks";
        $code = $r->code;
        $softdrinks = $r->softdrinks;
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'softdrinks' => [
                'required',
                'min:3',
                'max:120',
                Rule::unique('softdrinks')->where(function ($query)  use ($code, $softdrinks) {
                    return $query->where('isDelete', '=', '0')
                        ->where('softdrinks', '=',  $softdrinks)
                        ->where('code', '!=', $code);
                })
            ],
            'softdrinkImage' => 'nullable|image|mimes:jpg,png,jpeg',
            'type' => 'required|in:pop,bottle,juice',
            'price' => 'nullable',
            'drinksCount' => 'required',
            'description' => 'nullable',
        ];
        $messages = [
            'softdrinks.required' => 'Soft Drink name is required',
            'softdrinks.min' => 'Minimum of 3 characters are required.',
            'softdrinks.max' => 'Max characters exceeded.',
            'drinksCount.required' => 'Drinks Count is Required'
        ];
        $this->validate($r, $rules, $messages);
        $data = [
            'softdrinks' => ucwords(strtolower($r->softdrinks)),
            'type' => $r->type,
            'drinksType' => $r->type,
            'price' => $r->price,
            'drinksCount' => $r->drinksCount,
            'description' => $r->description,
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code
        ];
        $result = $this->model->doEdit($data, $table, $code);
        if ($filenew = $r->file('softDrinkImage')) {
            $imagename = $code . "." . $filenew->getClientOriginalExtension();
            $filenew->move('uploads/softdrinks', $imagename);
            $image_data = ['softDrinkImage' => $imagename];
            $image_update = $this->model->doEdit($image_data, $table, $code);
        }
        if ($result == true || $image_update == true) {
            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Soft Drink " . $code . " is updated.";
            $this->model->activity_log($data);
            //activity log end
            return redirect('softdrinks/list')->with('success', 'Soft Drink updated successfully');
        } else {
            return back()->with('error', 'Failed to update the soft drink');
        }
    }

    public function view(Request $request)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $softdrinks = Softdrinks::where('softdrinks.code', $code)->first();
            if (!empty($softdrinks)) {
                $data['queryresult'] = $softdrinks;
                return view('softdrinks.view', $data);
            }
        } else {
            return view('noright');
        }
    }


    // Add Soft Drink view
    public function add()
    {
        if ($this->rights != '' && $this->rights['insert'] == 1) {
            $insertRights = $this->rights['insert']; // Pass insert rights
            $viewRights = $this->rights['view'];
            return view('softdrinks.add', compact('insertRights', 'viewRights'));
        } else {
            return view('noright');
        }
    }

    // Store new Soft Drink
    public function store(Request $r)
    {
        $table = "softdrinks"; // Table name
        $softdrinkName = $r->softdrink;
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];

        $rules = [
            'softdrink' => [
                'required',
                'min:3',
                'max:120',
                Rule::unique('softdrinks', 'softdrinks')->where(function ($query) use ($softdrinkName) {
                    return $query->where('isDelete', 0)
                        ->where('softdrinks', $softdrinkName);
                })
            ],
            'price' => 'required|numeric|min:0.01',
            'drinksCount' => 'required|numeric|min:1',
            'type' => 'required|in:pop,bottle,juice',
            'description' => 'nullable',
        ];


        $messages = [
            'softdrink.required' => 'Soft Drink Name is required',
            'softdrink.min' => 'Minimum of 3 characters are required.',
            'softdrink.max' => 'Max characters exceeded.',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a number',
            'drinksCount.required' => 'Drinks Count is required',
            'drinksCount.numeric' => 'Drinks Count must be a number',
            'type.required' => 'Type is required',
            'type.in' => 'Invalid type selected'
        ];

        $this->validate($r, $rules, $messages);

        $typeValue = $r->drinksType;

        $data = [
            'softdrinks' => $r->softdrink,
            'price' => $r->price,
            'drinksCount' => $r->drinksCount,
            'description' => $r->description,
            'type' => $r->type,
            'drinksType' => $r->type,
            'isActive' => 1,
            'isDelete' => 0,
            'addIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'addID' => Auth::guard('admin')->user()->code
        ];


        // Handle  image upload
        if ($r->hasFile('softDrinkImage')) {
            $file = $r->file('softDrinkImage');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/softdrinks'), $filename);
            $data['softDrinkImage'] = $filename;
        }

        $code = $this->model->addNew($data, $table, 'SFD'); //prefix for Soft Drink

        if ($code) {
            $log = $currentdate->toDateTimeString() . "\t" . $ip . "\t" . Auth::guard('admin')->user()->code . " Soft Drink " . $code . " is added.";
            $this->model->activity_log($log);

            return redirect('softdrinks/list')->with('success', 'Soft Drink added successfully');
        } else {
            return back()->with('error', 'Failed to add Soft Drink');
        }
    }

    // delete softdrinks
    public function delete(Request $r)
    {
        $currentdate = Carbon::now();
        $code = $r->code;
        $ip = $_SERVER['REMOTE_ADDR'];
        $today = date('Y-m-d H:i:s');
        $table = 'softdrinks'; // Table name for soft drinks

        $data = [
            'isActive'   => 0,
            'isDelete'   => 1,
            'deleteIP'   => $ip,
            'deleteID'   => Auth::guard('admin')->user()->code,
            'deleteDate' => $today
        ];

        $datastring = $currentdate->toDateTimeString() . "\t" . $ip . "\t" . Auth::guard('admin')->user()->code . " Soft Drink " . $code . " is deleted.";
        $this->model->activity_log($datastring);

        $result = $this->model->doEditWithField($data, $table, 'code', $code);

        if ($result == true) {
            return response()->json(["status" => "success"], 200);
        } else {
            return response()->json(["status" => "fail"], 200);
        }
    }
}
