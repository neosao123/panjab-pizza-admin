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


// Developer - Shreyas Mahamuni
// Working Date - 22-11-2023

class PizzaPriceController extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('3.11', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }


    // Developer - Shreyas Mahamuni
    // Working Date - 22-11-2023
    // This function return view page of pizza price list 
    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            return view('pizzaprice.list');
        } else {
            return view('noright');
        }
    }

    // Developer - Shreyas Mahamuni
    // Working Date - 22-11-2023
    // This function get pizza price list
    public function getPizzaPrice(Request $req)
    {
        $tableName = "pizza_prices";
        $orderColumns = array("pizza_prices.*");
        $condition = array();
        $orderBy = array();
        $join = array();
        $like = array();
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
        $srno = $_GET['start'] + 1;
        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
                $actions = '<div class="btn-group">
                <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ti-settings"></i>
                </button>
                <div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">';
                if ($this->rights != '' && $this->rights['view'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("pizzaprice/view/" . $row->id) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("pizzaprice/edit/" . $row->id) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }

                $actions .= '</div>
               </div>';
                $data[] = array(
                    $srno,
                    $actions,
                    $row->size,
                    $row->price,       
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

    // Developer - Shreyas Mahamuni
    // Working Date - 22-11-2023
    // This function return view page of pizza price view 
    public function view(Request $request)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $pizzaPrice = DB::table('pizza_prices')->where("id", $request->id)->first();
            if (!empty($pizzaPrice)) {
                $data['queryresult'] = $pizzaPrice;
                return view('pizzaprice.view', $data);
            }
        } else {
            return view('noright');
        }
    }

    // Developer - Shreyas Mahamuni
    // Working Date - 22-11-2023
    // This function return view page of pizza price edit
    public function edit(Request $request)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $pizzaPrice = DB::table('pizza_prices')->where("id", $request->id)->first();
            if (!empty($pizzaPrice)) {
                $data['queryresult'] = $pizzaPrice;
                return view('pizzaprice.edit', $data);
            }
        } else {
            return view('noright');
        }
    }

    // Developer - Shreyas Mahamuni
    // Working Date - 22-11-2023
    // This function for update functionality
    public function update(Request $r)
    {
        $table = "pizza_prices";
        $id = $r->id;
       
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'pizzasize' => [
                'required',
            ],
            'pizzaprice' => [
                'required',
            ],
        ];
        $messages = [
            'pizzaprice.required' => 'Pizza Price is required',
        ];
        $this->validate($r, $rules, $messages);
        $data = [            
            'price' => number_format($r->pizzaprice, 2, '.', ''),
        ];
        $result = DB::table($table)->where('id', $id)->update($data);
        if ($result == true) {
            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Pizza Price " . $id . " is updated.";
            $this->model->activity_log($data);
            //activity log end
            return redirect('pizzaprice/list')->with('success', 'Pizza Prices updated successfully');
        } else {
            return back()->with('error', 'Failed to update the pizza price');
        }
    }
}
