<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use DB;

class SidesToppingsController extends Controller
{
    // Developer: Shreyas Mahamuni, Working Date: 12-02-2024
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('4.2', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    // Developer: Shreyas Mahamuni, Working Date: 12-02-2024
    public function getSidesToppings(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('sides_toppings.toppingsName' => $search);
        $condition = array('sides_toppings.isDelete' => array('=', 0));
        $orderBy = array('sides_toppings' . '.id' => 'DESC');
        $result = $this->model->selectQuery('sides_toppings.*', 'sides_toppings', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => ucwords(strtolower($item->toppingsName)));
            }
        }
        echo  json_encode($html);
    }

    // Developer: Shreyas Mahamuni, Working Date: 12-02-2024
    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            return view('sides-toppings.list');
        } else {
            return view('noright');
        }
    }

    // Developer: Shreyas Mahamuni, Working Date: 12-02-2024
    public function getSidesToppingsList(Request $req)
    {
        $toppings = $req->toppings;
        $search = $req->input('search.value');
        $tableName = "sides_toppings";
        $orderColumns = array("sides_toppings.*");
        $condition = array('sides_toppings.isDelete' => array('=', 0), 'sides_toppings.code' => array('=', $toppings));
        $orderBy = array('sides_toppings' . '.id' => 'DESC');
        $join = array();
        $like = array('sides_toppings.toppingsName' => $search, 'sides_toppings.code' => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
        $srno = $_GET['start'] + 1;
        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
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
                    $actions .= '<a class="dropdown-item" href="' . url("sides-toppings/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("sides-toppings/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                $actions .= '</div></div>';

                $data[] = array(
                    $srno,
                    $actions,
                    ucwords(strtolower($row->toppingsName)),
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

    // Developer: Shreyas Mahamuni, Working Date: 12-02-2024
    public function edit(Request $request)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $sides_toppings = DB::table('sides_toppings')->select('sides_toppings.*')->where('sides_toppings.code', $code)->first();
            if (!empty($sides_toppings)) {
                $data['queryresult'] = $sides_toppings;
                return view('sides-toppings.edit', $data);
            }
        } else {
            return view('noright');
        }
    }

    // Developer: Shreyas Mahamuni, Working Date: 12-02-2024
    public function add()
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            return view('sides-toppings.add', $data);
        } else {
            return view('noright');
        }
    }

    // Developer: Shreyas Mahamuni, Working Date: 12-02-2024
    public function store(Request $r)
    {
        $table = 'sides_toppings';
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $toppingsName = $r->toppingsName;

        $rules = [
            'toppingsName' => [
                'required',
                'min:3',
                'max:120',
                'regex:/^[a-zA-Z\s]+$/',
                Rule::unique('sides_toppings')->where(function ($query)  use ($toppingsName) {
                    return $query->where('isDelete', '=', '0')
                        ->where('toppingsName', '=',  $toppingsName);
                })
            ],
            'isActive' => 'nullable'
        ];
        $messages = [
            'toppingsName.required' => 'Toppings name is required',
            'toppingsName.regex' => 'The toppings name must be alphabetic characters & spaces.',
            'toppingsName.min' => 'Minimum of 3 characters are required.',
            'toppingsName.max' => 'Max characters exceeded.',
        ];
        $this->validate($r, $rules, $messages);

        $result = $this->model->selectQuery('sides_toppings.*', 'sides_toppings', array(), array(), '', '', '', '');

        if ($result) {
            $condition = $result->contains(function ($item) use ($r) {
                return $item->toppingsName == ucwords(strtolower($r->toppingsName));
            });
            if ($condition != 1) {
                $count = $r->countAs;
                $data = [
                    'toppingsName' => ucwords(strtolower($r->toppingsName)),
                    'isActive' => $r->isActive == "" ? '0' : 1,
                    'isDelete' => 0,
                    'addIP' => $ip,
                    'addDate' => $currentdate->toDateTimeString(),
                ];
                $res = $this->model->addNew($data, $table, 'STOP');
                if ($res) {
                    return redirect('sides-toppings/list')->with('success', 'Record added successfully', $res);
                }
                return back()->with('error', 'Failed to add the record');
            } else {
                return back()->with('error', 'Failed to add the record, Toppings name already exist');
            }
        }
    }

    // Developer: Shreyas Mahamuni, Working Date: 12-02-2024
    public function update(Request $r)
    {
        $table = "sides_toppings";
        $code = $r->code;
        $toppingsName = $r->toppingsName;
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'toppingsName' => [
                'required',
                'min:3',
                'max:120',
                'regex:/^[a-zA-Z\s]+$/',
                Rule::unique('sides_toppings')->where(function ($query)  use ($code, $toppingsName) {
                    return $query->where('isDelete', '=', '0')
                        ->where('toppingsName', '=',  $toppingsName)
                        ->where('code', '!=', $code);
                })
            ],
            'isActive' => 'nullable'
        ];
        $messages = [
            'toppingsName.required' => 'Toppings name is required',
            'toppingsName.regex' => 'The toppings name must be alphabetic characters & spaces.',
            'toppingsName.min' => 'Minimum of 3 characters are required.',
            'toppingsName.max' => 'Max characters exceeded.',
        ];
        $this->validate($r, $rules, $messages);
        $data = [
            'toppingsName' => ucwords(strtolower($r->toppingsName)),
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code
        ];
        $result = $this->model->doEdit($data, $table, $code);
        if ($result == true) {
            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Topping " . $code . " is updated.";
            $this->model->activity_log($data);
            //activity log end
            return redirect('sides-toppings/list')->with('success', 'Topping updated successfully');
        } else {
            return back()->with('error', 'Failed to update the topping');
        }
    }

    // Developer: Shreyas Mahamuni, Working Date: 12-02-2024
    public function view(Request $request)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $sides_toppings = DB::table('sides_toppings')->select('sides_toppings.*')->where('sides_toppings.code', $code)->first();
            if (!empty($sides_toppings)) {
                $data['queryresult'] = $sides_toppings;
                return view('sides-toppings.view', $data);
            }
        } else {
            return view('noright');
        }
    }
}
