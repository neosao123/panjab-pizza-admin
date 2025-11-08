<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;

class PizzasCategoryController extends Controller
{


    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('8.1', $this->role);
            if (!$this->rights && $this->rights == "") {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }
    //seemashelar@neosao
    //category list

    public function getCategories(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('signaturepizzacategory.category_name' => $search);
        $condition = array('signaturepizzacategory.isDelete' => array('=', 0), 'signaturepizzacategory.type' => array('=', 2));
        $orderBy = array('signaturepizzacategory' . '.id' => 'DESC');
        $result = $this->model->selectQuery('signaturepizzacategory.*', 'signaturepizzacategory', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => ucwords(strtolower($item->category_name)));
            }
        }
        echo  json_encode($html);
    }

    //seemashelar@neosao
    //pizzas category list page
    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            return view('pizzas-category.list');
        } else {
            return view('noright');
        }
    }

    //seemashelar@neosao
    //pizzas category list

    public function getCategoriesList(Request $req)
    {
        $category = $req->category;
        $search = $req->input('search.value');
        $tableName = "signaturepizzacategory";
        $orderColumns = array("signaturepizzacategory.*");
        $condition = array('signaturepizzacategory.type' => array('=', 2), 'signaturepizzacategory.isDelete' => array('=', 0), 'signaturepizzacategory.code' => array('=', $category));
        $orderBy = array('signaturepizzacategory' . '.id' => 'DESC');
        $join = array();
        $like = array('signaturepizzacategory.category_name' => $search, 'signaturepizzacategory.code' => $search);
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
                    $actions .= '<a class="dropdown-item" href="' . url("pizzas-category/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("pizzas-category/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights != '' && $this->rights['delete'] == 1) {
                    $actions .= '<a style="cursor:pointer;"class="dropdown-item delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash mr-2" href></i> Delete</a>';
                }
                $actions .= '</div>
            </div>';
                $data[] = array(
                    $srno,
                    $actions,
                    ucwords(strtolower($row->category_name)),
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
    //seemashelar@neosao
    //edit category

    public function edit(Request $request)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $category = DB::table('signaturepizzacategory')->select('signaturepizzacategory.*')
                ->where('signaturepizzacategory.code', $code)
                ->where('type', 2)
                ->first();

            $data['queryresult'] = $category;
            return view('pizzas-category.edit', $data);
        } else {
            return view('noright');
        }
    }
    //seemashelar@neosao
    //add category

    public function add()
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            return view('pizzas-category.add', $data);
        } else {
            return view('noright');
        }
    }

    //seemashelar@neosao
    //store category

    public function store(Request $r)
    {
        $table = "signaturepizzacategory";
        $code = $r->code;
        $categoryName = $r->categoryName;
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'categoryName' => [
                'required',
                'min:3',
                'max:150',
                Rule::unique('signaturepizzacategory', 'category_name')->where(function ($query)  use ($code, $categoryName) {
                    return $query->where('isDelete', '=', '0')->where("type", 2);
                })
            ]
        ];
        $messages = [
            'categoryName.required' => 'Category name is required',
            'categoryName.min' => 'Minimum of 3 characters are required.',
            'categoryName.max' => 'Max characters exceeded.',
        ];
        $this->validate($r, $rules, $messages);

        $data = [
            'category_name' => ucwords(strtolower($r->categoryName)),
            'type' => 2,
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'addIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
        ];
        $res = $this->model->addNew($data, $table, 'PCAT');
        if ($res) {
            return redirect('pizzas-category/list')->with('success', 'Record added successfully', $res);
        }
        return back()->with('error', 'Failed to add the record');
    }

    //seemashelar@neosao
    //update category


    public function update(Request $r)
    {
        $table = "signaturepizzacategory";
        $code = $r->code;
        $categoryName = $r->categoryName;
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'categoryName' => [
                'required',
                'min:3',
                'max:150',
                Rule::unique('signaturepizzacategory', 'category_name')->where(function ($query)  use ($code, $categoryName) {
                    return $query->where('isDelete', '=', '0')
                        ->where("type", 2)
                        ->where('category_name', '=',  $categoryName)
                        ->where('code', '!=', $code);
                })
            ]
        ];
        $messages = [
            'categoryName.required' => 'Category name is required',
            'categoryName.min' => 'Minimum of 3 characters are required.',
            'categoryName.max' => 'Max characters exceeded.',
        ];
        $this->validate($r, $rules, $messages);

        $data = [
            'category_name' => ucwords(strtolower($r->categoryName)),
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code
        ];
        $result = $this->model->doEdit($data, $table, $code);
        if ($result == true) {
            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Signature Pizza Category " . $code . " is updated.";
            $this->model->activity_log($data);
            //activity log end
            return redirect('pizzas-category/list')->with('success', 'Pizzas Category updated successfully');
        } else {
            return back()->with('error', 'Failed to update the Pizzas Category');
        }
    }

    //seemashelar@neosao
    //view category

    public function view(Request $request)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $category = DB::table('signaturepizzacategory')->select('signaturepizzacategory.*')
                ->where('signaturepizzacategory.code', $code)
                ->where('type', 2)
                ->first();

            $data['queryresult'] = $category;
            return view('pizzas-category.view', $data);
        } else {
            return view('noright');
        }
    }
    //seemashelar@neosao
    //delete category

    public function delete(Request $r)
    {

        $currentdate = Carbon::now();
        $code = $r->code;
        $ip = $_SERVER['REMOTE_ADDR'];
        $today = date('Y-m-d H:i:s');
        $table = 'signaturepizzacategory';
        $data = ['isActive' => 0, 'isDelete' => 1, 'deleteIP' => $ip, 'deleteID' => Auth::guard('admin')->user()->code, 'deleteDate' => $today];

        //activity log start
        $datastring = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Category " . $code  . " is deleted.";
        $this->model->activity_log($datastring);
        //activity log end 

        $result = $this->model->doEditWithField($data, $table, 'code', $code);
        if ($result == true) {
            return response()->json(["status" => "success"], 200);
        } else {
            return response()->json(["status" => "fail"], 200);
        }
    }
}
