<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\Specialoffer;
use App\Models\Softdrinks;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Cheese;
use App\Models\Crust;
use App\Models\CrustType;
use App\Models\Specialbases;
use App\Models\Spices;
use App\Models\Sauce;
use App\Models\Cook;
use App\Models\Toppings;
use DB;



class SignaturePizzaController extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('7.2', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }
    //seemashelar@neosao
    //signature pizza list

    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['insertRights'] = $this->rights['insert'];
            return view('signature-pizza.list', $data);
        } else {
            return view('noright');
        }
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


    public function getCrustType(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('crust_type.crustType' => $search);
        $condition = array('crust_type.isDelete' => array('=', 0));
        $orderBy = array('crust_type' . '.id' => 'DESC');
        $result = $this->model->selectQuery('crust_type.*', 'crust_type', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => $item->crustType);
            }
        }
        echo  json_encode($html);
    }

    public function getCrust(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('crust.crust' => $search);
        $condition = array('crust.isDelete' => array('=', 0));
        $orderBy = array('crust' . '.id' => 'DESC');
        $result = $this->model->selectQuery('crust.*', 'crust', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => $item->crust);
            }
        }
        echo  json_encode($html);
    }

     public function getCheese(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('cheese.cheese' => $search);
        $condition = array('cheese.isDelete' => array('=', 0));
        $orderBy = array('cheese' . '.id' => 'DESC');
        $result = $this->model->selectQuery('cheese.*', 'cheese', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => $item->cheese);
            }
        }
        echo  json_encode($html);
    }

     public function getCook(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('cook.cook' => $search);
        $condition = array('cook.isDelete' => array('=', 0));
        $orderBy = array('cook' . '.id' => 'DESC');
        $result = $this->model->selectQuery('cook.*', 'cook', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => $item->cook);
            }
        }
        echo  json_encode($html);
    }

    //seemashelar@neosao
    //signature pizza list


    public function getSignaturePizza(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('signaturepizza.pizza_name' => $search);
        $condition = array('signaturepizza.isDelete' => array('=', 0));
        $orderBy = array('signaturepizza' . '.id' => 'DESC');
        $result = $this->model->selectQuery('signaturepizza.*', 'signaturepizza', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => ucwords(strtolower($item->pizza_name)));
            }
        }
        echo  json_encode($html);
    }

    //seemashelar@neosao
    //category list

    public function getCategories(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('signaturepizzacategory.category_name' => $search);
        $condition = array('signaturepizzacategory.isDelete' => array('=', 0), 'signaturepizzacategory.type' => array('=', 1));
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
    //signature pizza list

    public function getSignaturePizzaList(Request $req)
    {
        $category = $req->category;
        $pizza = $req->pizza;
        $search = $req->input('search.value');
        $tableName = "signaturepizza";
        $orderColumns = array("signaturepizza.*", "signaturepizzacategory.category_name");
        $condition = array('signaturepizza.isDelete' => array('=', 0), 'signaturepizzacategory.code' => array('=', $category), 'signaturepizza.code' => array('=', $pizza));
        $orderBy = array('signaturepizza' . '.id' => 'DESC');
        $groupBy = array();
        $join = array('signaturepizzacategory' => array('signaturepizzacategory.code', 'signaturepizza.category_code'));
        $joinType = array('signaturepizzacategory' => 'left');
        $like = array('signaturepizzacategory.category_name' => $search, 'signaturepizza.pizza_name' => $search, 'signaturepizza.pizza_subtitle' => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQueryWithGroupBy($orderColumns, $tableName, $join, $condition, $orderBy, $groupBy, $like, $limit, $offset, $extraCondition, $joinType);
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
                    $actions .= '<a class="dropdown-item" href="' . url("signature-pizza/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("signature-pizza/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights != '' && $this->rights['delete'] == 1) {
                    $actions .= '<a style="cursor:pointer;"class="dropdown-item delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash mr-2" href></i> Delete</a>';
                }
                $actions .= '</div>
            </div>';
                $data[] = array(
                    $srno,
                    $actions,
                    ucwords(strtolower($row->pizza_name)),
                    ucwords(strtolower($row->pizza_subtitle)),
                    ucwords(strtolower($row->category_name)),

                    $status,
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

    //seemashelar@neosao
    //add signature pizza 

    public function add()
    {
        if ($this->rights != '' && $this->rights['insert'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $data['cheese'] = Cheese::where("isActive", 1)->get();
            $data['crust'] = Crust::where("isActive", 1)->get();
            $data['crustType'] = CrustType::where("isActive", 1)->get();
            $data['specialBase'] = Specialbases::where("isActive", 1)->get();
            $data['spices'] = Spices::where("isActive", 1)->get();
            $data['sauce'] = Sauce::where("isActive", 1)->get();
            $data['cook'] = Cook::where("isActive", 1)->get();
            $data['toppingAsOne'] = Toppings::where("isActive", 1)->where("topping_type", 'regular')->get();
            $data['toppingAsTwo'] = Toppings::where("isActive", 1)->where("topping_type", 'non-regular')->get();
            $data['toppingFree'] = Toppings::where("isActive", 1)->where("isPaid", 0)->get();
            $data['pizzaPrices'] = DB::table('pizza_prices')->where('isActive', 1)->orderBy('order_column', 'ASC')->get();
            return view('signature-pizza.add', $data);
        } else {
            return view('backend.noright');
        }
    }

    public function store(Request $r)
    {
        $table = "signaturepizza";
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];

        $rules = [
            'name' => [
                'required',
                'min:3',
                'max:150',
                Rule::unique('signaturepizza', 'pizza_name')->where("isDelete", 0)
            ],
            'subtitle' => 'nullable|min:3|max:150',
            'cheese' => 'required',
            'crust' => 'required',
            'crustType' => 'required',
            'specialBase' => 'required',
            'spices' => 'required',
            'cook' => 'required',
            'sauce' => 'required',
            'pizzaPrice.*.price' => 'required|gte:0',
            'description' => 'nullable|min:2'
        ];

        $messages = [
            'name.required' => 'Signature pizza name is required.',
            'name.unique' => 'This signature pizza name already exists. Please choose a different name.',
            'name.min' => 'The signature pizza name must be at least 3 characters.',
            'name.max' => 'The signature pizza name may not be greater than 150 characters.',
            'cheese.required' => 'Cheese selection is required.',
            'crust.required' => 'Crust selection is required.',
            'crustType.required' => 'Crust type is required.',
            'specialBase.required' => 'Special base selection is required.',
            'spices.required' => 'Spice level is required.',
            'cook.required' => 'Cook preference is required.',
            'sauce.required' => 'Sauce is required.',
            'pizzaPrice.*.price.required' => 'Each pizza price is required.',
            'pizzaPrice.*.price.gte' => 'Each pizza price should be greater than or equal to 0 (zero).',
            'description.min' => 'Description must be at least 2 characters.'
        ];

        $this->validate($r, $rules, $messages);

        $hasGreaterThanZero = false;
        // Loop through pizza prices to check the condition
        foreach ($r->pizzaPrice as $item) {
            if ($item['price'] > 0) {
                $hasGreaterThanZero = true;
                break;
            }
        }

        // If no price is greater than 0, show a Toastr error and redirect back
        if (!$hasGreaterThanZero) {
            return redirect()->back()->with('error', 'At least one pizza price must be greater than 0.');
        }

        $pizzaPrices = [];
        // Loop over the pizza prices and prepare the array
        foreach ($r->pizzaPrice as $item) {
            $isDefault = isset($item['isDefault']) && $item['isDefault'] == 1 ? 1 : 0;

            $pizzaPrices[] = [
                'size' => $item['size'],
                'price' => $item['price'],
                'shortcode' => $item['shortcode'],
                'isDefault' => $isDefault,
            ];
        }
        $data = [
            'category_code' => $r->category,
            'pizza_name' => ucwords(strtolower($r->name)),
            'pizza_subtitle' => $r->subtitle,
            'pizza_prices' => json_encode($pizzaPrices),  // Store the JSON string
            'cheese' => json_encode(json_decode($r->cheese, true)),  // Store the JSON string for cheese selection
            'crust' => json_encode(json_decode($r->crust, true)),
            'crust_type' => json_encode(json_decode($r->crustType, true)),
            'special_base' => json_encode(json_decode($r->specialBase, true)),
            'spices' => json_encode(json_decode($r->spices, true)),
            'sauce' => json_encode(json_decode($r->sauce, true)),
            'cook' => json_encode(json_decode($r->cook, true)),
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'addIP' => $ip,
            'description' => $r->description,
            'addDate' => $currentdate->toDateTimeString(),
            'addID' => Auth::guard('admin')->user()->code,
        ];

        // Process and store toppings as JSON arrays
        $data['topping_as_1'] = $r->has('topping_as_one') ? json_encode(array_map(function ($item) {
            return json_decode($item, true);  // Decoding each item in the array, if needed
        }, $r->topping_as_one)) : json_encode([]);

        $data['topping_as_2'] = $r->has('topping_as_two') ? json_encode(array_map(function ($item) {
            return json_decode($item, true);  // Decoding each item in the array, if needed
        }, $r->topping_as_two)) : json_encode([]);

        $data['topping_as_free'] = $r->has('free_topping') ? json_encode(array_map(function ($item) {
            return json_decode($item, true);  // Decoding each item in the array, if needed
        }, $r->free_topping)) : json_encode([]);

        // Insert the record and retrieve the current ID
        $currentId = $this->model->addNew($data, $table, 'SP');

        if ($currentId) {
            // Handle image upload
            if ($filenew = $r->file('signaturepizzaimage')) {
                $imagename = $currentId . "." . $filenew->getClientOriginalExtension();
                $filenew->move('uploads/signature-pizza', $imagename);
                $image_data = ['pizza_image' => $imagename];
                $image_update = $this->model->doEdit($image_data, $table, $currentId);
            }

            // Activity log entry
            $logMessage = $currentdate->toDateTimeString() . " " . $ip . " " . Auth::guard('admin')->user()->code . " Signature Pizza " . $currentId . " is added";
            $this->model->activity_log($logMessage);

            return redirect('signature-pizza/list')->with('success', 'Record added successfully');
        }

        return back()->with('error', 'Failed to add the record');
    }

    //seemashelar@neosao
    //edit signature pizza

    public function edit(Request $request)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $signaturepizza = DB::table("signaturepizza")
                ->join("signaturepizzacategory", "signaturepizzacategory.code", "=", "signaturepizza.category_code")
                ->select('signaturepizza.*', 'signaturepizzacategory.category_name')
                ->where('signaturepizza.code', $code)
                ->first();
            if (!empty($signaturepizza)) {
                $data['cheese'] = Cheese::where("isActive", 1)->get();
                $data['crust'] = Crust::where("isActive", 1)->get();
                $data['crustType'] = CrustType::where("isActive", 1)->get();
                $data['specialBase'] = Specialbases::where("isActive", 1)->get();
                $data['spices'] = Spices::where("isActive", 1)->get();
                $data['sauce'] = Sauce::where("isActive", 1)->get();
                $data['cook'] = Cook::where("isActive", 1)->get();
                $data['toppingAsOne'] = Toppings::where("isActive", 1)->where("topping_type", 'regular')->get();
                $data['toppingAsTwo'] = Toppings::where("isActive", 1)->where("topping_type", 'non-regular')->get();
                $data['toppingFree'] = Toppings::where("isActive", 1)->where("isPaid", 0)->get();
                $data['pizzaPrices'] = DB::table('pizza_prices')->where('isActive', 1)->orderBy('order_column', 'ASC')->get();
                $data['queryresult'] = $signaturepizza;
                return view('signature-pizza.edit', $data);
            }
        } else {
            return view('noright');
        }
    }
    //seemashelar@neosao
    //update signature pizza
    public function update(Request $r)
    {
        $table = "signaturepizza";
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $code = $r->code;
        $rules = [
            'name' => [
                'required',
                'min:3',
                'max:150',
                Rule::unique('signaturepizza', 'pizza_name')
                    ->where('isDelete', 0)
                    ->ignore($code, 'code')
            ],
            'category' => 'required',
            'subtitle' => 'nullable|min:3|max:150',
            'cheese' => 'required',
            'crust' => 'required',
            'crustType' => 'required',
            'specialBase' => 'required',
            'spices' => 'required',
            'sauce' => 'required',
            'cook' => 'required',
            'pizzaPrice.*.price' => 'required|gte:0',
            'description' => 'nullable|min:2'
        ];

        $messages = [
            'name.required' => 'Signature pizza name is required.',
            'name.unique' => 'This signature pizza name already exists. Please choose a different name.',
            'name.min' => 'The signature pizza name must be at least 3 characters.',
            'name.max' => 'The signature pizza name may not be greater than 150 characters.',
            'cheese.required' => 'Cheese selection is required.',
            'category.required' => 'Category is required.',
            'crust.required' => 'Crust selection is required.',
            'crustType.required' => 'Crust type is required.',
            'specialBase.required' => 'Special base selection is required.',
            'spices.required' => 'Spice level is required.',
            'sauce.required' => 'Sauce is required',
            'cook.required' => 'Cook preference is required.',
            'pizzaPrice.*.price.required' => 'Each pizza price is required.',
            'pizzaPrice.*.price.gte' => 'Each pizza price should be greater than or equal to 0 (zero).',
            'description.min' => 'Description must be at least 2 characters.'
        ];

        $this->validate($r, $rules, $messages);

        $hasGreaterThanZero = false;
        // Loop through pizza prices to check the condition
        foreach ($r->pizzaPrice as $item) {
            if ($item['price'] > 0) {
                $hasGreaterThanZero = true;
                break;
            }
        }

        // If no price is greater than 0, show a Toastr error and redirect back
        if (!$hasGreaterThanZero) {
            return redirect()->back()->with('error', 'At least one pizza price must be greater than 0.');
        }

        $pizzaPrices = [];
        $defaultShortcode = $r->input('isDefault'); // Get the shortcode of the selected default pizza price

        // Get the pizza price data from the form
        $pizzaPriceItems = $r->input('pizzaPrice', []);

        // Loop through the pizza prices and set the default
        foreach ($pizzaPriceItems as $item) {
            // Add the price and size to the pizzaPrices array
            $pizzaPrices[] = [
                'size' => $item['size'],
                'price' => $item['price'],
                'shortcode' => $item['shortcode'],
                'isDefault' => $item['shortcode'] == $defaultShortcode ? 1 : 0, // Set the default if the shortcode matches
            ];
        }



        $data = [
            'category_code' => $r->category,
            'pizza_name' => ucwords(strtolower($r->name)),
            'pizza_subtitle' => $r->subtitle,
            'pizza_prices' => json_encode($pizzaPrices),  // Store the JSON string
            'cheese' => json_encode(json_decode($r->cheese, true)),  // Store the JSON string for cheese selection
            'crust' => json_encode(json_decode($r->crust, true)),
            'crust_type' => json_encode(json_decode($r->crustType, true)),
            'special_base' => json_encode(json_decode($r->specialBase, true)),
            'spices' => json_encode(json_decode($r->spices, true)),
            'sauce' => json_encode(json_decode($r->sauce, true)),
            'cook' => json_encode(json_decode($r->cook, true)),
            'isActive' => $r->isActive == "" ? '0' : 1,
            'description' => $r->description,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code,
        ];

        // Process and store toppings as JSON arrays
        $data['topping_as_1'] = $r->has('topping_as_one') ? json_encode(array_map(function ($item) {
            return json_decode($item, true);  // Decoding each item in the array, if needed
        }, $r->topping_as_one)) : json_encode([]);

        $data['topping_as_2'] = $r->has('topping_as_two') ? json_encode(array_map(function ($item) {
            return json_decode($item, true);  // Decoding each item in the array, if needed
        }, $r->topping_as_two)) : json_encode([]);

        $data['topping_as_free'] = $r->has('free_topping') ? json_encode(array_map(function ($item) {
            return json_decode($item, true);  // Decoding each item in the array, if needed
        }, $r->free_topping)) : json_encode([]);

        if ($filenew = $r->file('signaturepizzaimage')) {
            $imagename = $code . "." . $filenew->getClientOriginalExtension();
            $filenew->move('uploads/signature-pizza', $imagename);
            $data['pizza_image'] = $imagename;
        }

        $result = $this->model->doEdit($data, $table, $code);
        if ($result == true) {
            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Signature Pizza " . $code . " is updated.";
            $this->model->activity_log($data);
            //activity log end
            return redirect('signature-pizza/list')->with('success', 'Signature pizza updated successfully');
        } else {
            return back()->with('error', 'Failed to update the signature pizza');
        }
    }
    //seemashelar@neosao
    //delete image
    public function deleteImage(Request $r)
    {
        $imgNm = $r->value;
        $code = $r->code;
        $data = array(
            'pizza_image' => '',
        );
        if (!empty($data)) {
            unlink('uploads/signature-pizza/' . $imgNm);
            echo $resultData = $this->model->doEdit($data, 'signaturepizza', $code);
        } else {
            echo 'false';
        }
    }

    //seemashelar@neosao
    //delete signature pizza
    public function delete(Request $r)
    {
        $currentdate = Carbon::now();
        $code = $r->code;
        $ip = $_SERVER['REMOTE_ADDR'];
        $today = date('Y-m-d H:i:s');
        $table = 'signaturepizza';
        $data = ['isActive' => 0, 'isDelete' => 1, 'deleteIP' => $ip, 'deleteID' => Auth::guard('admin')->user()->code, 'deleteDate' => $today];

        //activity log start
        $datastring = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Signature Pizza " . $code . "  is deleted.";
        $this->model->activity_log($datastring);
        //activity log end

        $result = $this->model->doEditWithField($data, $table, 'code', $code);

        if ($result == true) {
            return response()->json(["status" => "success"], 200);
        } else {
            return response()->json(["status" => "fail"], 200);
        }
    }
    //seemashelar@neosao
    //view signature pizza

    public function view(Request $request)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $signaturepizza = DB::table("signaturepizza")
                ->join("signaturepizzacategory", "signaturepizzacategory.code", "=", "signaturepizza.category_code")
                ->select('signaturepizza.*', 'signaturepizzacategory.category_name')
                ->where('signaturepizza.code', $code)
                ->first();
            if (!empty($signaturepizza)) {
                $data['cheese'] = Cheese::where("isActive", 1)->get();
                $data['crust'] = Crust::where("isActive", 1)->get();
                $data['crustType'] = CrustType::where("isActive", 1)->get();
                $data['specialBase'] = Specialbases::where("isActive", 1)->get();
                $data['spices'] = Spices::where("isActive", 1)->get();
                $data['sauce'] = Sauce::where("isActive", 1)->get();
                $data['cook'] = Cook::where("isActive", 1)->get();
                $data['toppingAsOne'] = Toppings::where("isActive", 1)->where("countAs", 1)->where("isPaid", 1)->get();
                $data['toppingAsTwo'] = Toppings::where("isActive", 1)->where("countAs", 2)->where("isPaid", 1)->get();
                $data['toppingFree'] = Toppings::where("isActive", 1)->where("isPaid", 0)->get();
                $data['pizzaPrices'] = DB::table('pizza_prices')->where('isActive', 1)->orderBy('order_column', 'ASC')->get();
                $data['queryresult'] = $signaturepizza;
                return view('signature-pizza.view', $data);
            }
        } else {
            return view('noright');
        }
    }
}
