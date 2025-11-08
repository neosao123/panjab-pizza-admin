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
use DB;


class SpecialOfferController extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('6.1', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['insertRights'] = $this->rights['insert'];
            return view('specialoffer.list', $data);
        } else {
            return view('noright');
        }
    }

    public function getSpecialOffers(Request $r)
    {
        $html = [];
        $search = $r->search;
        $like = array('specialoffer.name' => $search);
        $condition = array('specialoffer.isDelete' => array('=', 0));
        $orderBy = array('specialoffer' . '.id' => 'DESC');
        $result = $this->model->selectQuery('specialoffer.*', 'specialoffer', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => ucwords(strtolower($item->name)));
            }
        }
        echo  json_encode($html);
    }

    public function getSpecialOfferByType(Request $r)
    {
        $html = [];

        $search = $r->search;
        $type = $r->type;

        $query = DB::table("sidemaster")
            ->select("sidemaster.*")
            ->where("sidemaster.isDelete", 0);

        if ($type != "") {
            $query->where('sidemaster.sidename', 'like', "%$search%");
        }

        $result = $query->whereIn('sidemaster.type', (array)$type) // Ensure $type is always treated as an array
            ->orderBy("sidemaster.id", "desc")
            ->get();

        // Check if $result is not empty before processing
        if ($result && $result->count() > 0) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => ucwords(strtolower($item->sidename)), 'type' => $item->type);
            }
        }

        echo json_encode($html);
    }


    public function getSize(Request $r)
    {
        $html = [];
        $search = $r->search;
        $sideCode = $r->sideCode;
        $query = DB::table("sidelineentries")
            ->select("sidelineentries.*")
            ->where("sidelineentries.isDelete", 0);
        if ($r->has('search') && trim($r->search) != "") {
            $query->where(function ($q) use ($search) {
                $q->where('sidelineentries.size', 'like', '%' . $search . '%')
                    ->orWhere('sidelineentries.price', 'like', '%' . $search . '%');
            });
        }
        $query->where("sidelineentries.sidemasterCode", $sideCode);
        $result = $query->orderBy("sidelineentries.id", "desc")->get();
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => $item->size . " " . $item->price);
            }
        }
        echo  json_encode($html);
    }

    public function getSpecialOfferList(Request $req)
    {
        $specialoffer = $req->specialoffer;
        $search = $req->input('search.value');
        $tableName = "specialoffer";
        $orderColumns = array("specialoffer.*");
        $condition = array('specialoffer.isDelete' => array('=', 0), 'specialoffer.code' => array('=', $specialoffer));
        $orderBy = array('specialoffer' . '.id' => 'DESC');
        $join = array();
        $like = array('specialoffer.noofPizza' => $search, 'specialoffer.price' => $search, 'specialoffer.name' => $search, 'specialoffer.noofToppings' => $search, 'specialoffer.noofDips' => $search, 'specialoffer.noofSides' => $search);
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

                $showOnClient = '<span class="badge badge-danger"> False </span>';
                if ($row->showOnClient == 1) {
                    $showOnClient = '<span class="badge badge-success">True</span>';
                }

                $specialOfferImage = '';
                if ($row->specialofferphoto != '') {
                    $specialOfferImage = '<img src="' . url("uploads/specialoffer/" . $row->specialofferphoto) . "?v=" . time() . '" height="50" width="50" alt="Special Offer Image">';
                }
                $actions = '<div class="btn-group">
                            <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ti-settings"></i>
                            </button>
                            <div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">';
                if ($this->rights != '' && $this->rights['view'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("specialoffer/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("specialoffer/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights != '' && $this->rights['delete'] == 1) {
                    $actions .= '<a style="cursor:pointer;"class="dropdown-item delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash mr-2" href></i> Delete</a>';
                }

                $actions .= '</div>
						</div>';
                $subtitle = trim($row->subtitle) != ""  ? " (" . $row->subtitle . ")" : "";
                $name = $row->name . $subtitle;
                $data[] = array(
                    $srno,
                    $actions,
                    $name,
                    $row->noofPizza,
                    //$row->noofToppings,
                    $row->noofDips,
                    $row->noofSides,
                    $status,
                    $showOnClient
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

    public function add()
    {
        if ($this->rights != '' && $this->rights['insert'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $data['pops'] = Softdrinks::where("isActive", 1)->where("type", "pop")->get();
            $data['bottle'] = Softdrinks::where("isActive", 1)->where("type", "bottle")->get();
            $data['pizzaPrices'] = DB::table('pizza_prices')->where('isActive', 1)->orderBy('order_column', 'ASC')->get();
            return view('specialoffer.add', $data);
        } else {
            return view('backend.noright');
        }
    }

    public function store(Request $r)
    {

        $table = "specialoffer";
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'name' => 'required|min:3|max:150',
            'subtitle' => 'nullable|min:3|max:150',
            'dealType' => 'required',
            'description' => 'nullable|min:2|max:300',
            'specialofferphoto' => 'nullable|image|mimes:jpg,png,jpeg',
            //'noofToppings' => 'required',
            'noofDips' => 'required',
            'noofSides' => 'required',
            'noofPizza' => 'required',
            'type' => 'nullable',
            'pops' => 'nullable',
            'bottle' => 'nullable',
            'showOnClient' => 'nullable', // Developer: Shreyas Mahamuni, Wokring Date: 21-12-2024
            'limited_offer' => 'nullable',
            'start_date' => [
                'nullable',
                'required_if:limited_offer,1',
                'after:today' // Must be greater than today
            ],
            'end_date' => [
                'nullable',
                'required_if:limited_offer,1',
                'after:start_date' // Must be greater than start_date
            ],
            'pizzaPrice.*.price'    => 'required|gte:0'
        ];
        $messages = [
            'name.required' => 'Special offer name is required.',
            'name.min' => 'Minimum of 3 characters are required.',
            'name.max' => 'Max characters exceeded.',
            'subtitle.min' => 'Minimum of 3 characters are required.',
            'subtitle.max' => 'Max characters exceeded.',
            'description.min' => 'Minimum of 2 characters are required.',
            'description.max' => 'Max characters exceeded.',
            //'noofToppings.required' => 'Number of toppings is required.',
            'noofDips.required' => 'Number of dips is required.',
            'noofSides.required' => 'Number of sides is required.',
            'noofPizza.required' => 'Number of pizza is required.',
            'dealType.required' => 'Deal Type is required.',
            'start_date.required_if' => 'Offer start date is required',
            'start_date.date'    => 'Offer start date must a valid date',
            'start_date.after'    => 'Offer start date must be greater than today',
            'end_date.required_if' => 'Offer end date is required',
            'end_date.date'    => 'Offer end date must a valid date',
            'end_date.after'    => 'Offer end date must be greater than start date',
            'pizzaPrice.*.price.required'    => 'Pizza Prices are required',
            'pizzaPrice.*.price.gte'    => 'Pizza Prices should be greater than or equal to 0 (zero).'
        ];

        //dd($r->pizzaPrice);

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

        foreach ($r->pizzaPrice as $item) {
            $pizzaPrices[] = [
                'size' => $item['size'],
                'price' => $item['price'],
                'shortcode' => $item['shortcode'],
            ];
        }

        $data = [
            'name' => ucwords(strtolower($r->name)),
            'subtitle' => $r->subtitle,
            'dealType' => $r->dealType,
            'noofToppings' => $r->noofToppings ?? 0,
            'noofDips' => $r->noofDips,
            'noofSides' => $r->noofSides,
            'noofPizza' => $r->noofPizza,
            //'price' => $r->price,
            //'extraLargePrice' => $r->extraLargePrice,
            'pops' => $r->pops,
            'bottle' => $r->bottle,
            'type' => json_encode($r->type),
            'description' => $r->description,
            'showOnClient' => $r->showOnClient == null ? 0 : $r->showOnClient,   // Developer: Shreyas Mahamuni, Wokring Date: 21-12-2024
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'addIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'addID' => Auth::guard('admin')->user()->code,
            'limited_offer' => $r->limited_offer ?? 0,
            'pizza_prices' => stripslashes(json_encode($pizzaPrices))
        ];

        if ($r->start_date != "" && $r->end_date != "") {
            $data['start_date'] = date('Y-m-d H:i:00', strtotime($r->start_date));
            $data['end_date'] = date('Y-m-d H:i:00', strtotime($r->end_date));
        }

        $currentId = $this->model->addNew($data, $table, 'SPO');
        if ($currentId) {
            if ($r->has('sides')) {
                $sides = $r->sides;
                $size = $r->size;
                for ($i = 0; $i < count($sides); $i++) {
                    $subdata = [
                        'specialOfferCode' => $currentId,
                        'sidemasterCode' => $sides[$i],
                        'sidelineentries' => $size[$i],
                        'isActive' => '1',
                        'isDelete' => '0',
                    ];

                    $subdata['addDate'] = date('Y-m-d H:i:s');
                    $subdata['addIP'] = $ip;
                    $subdata['addID'] = Auth::guard('admin')->user()->code;
                    $code = $this->model->addNew($subdata, 'specialofferlineentries', 'SOL');

                    //activity log start
                    $data = $currentdate->toDateTimeString() . "	" . $ip . "	" . Auth::guard('admin')->user()->code .    "	Side Offer Line Entries " . $code . " is added";
                    $this->model->activity_log($data);
                    //activity log end
                }
            }

            if ($filenew = $r->file('specialofferphoto')) {
                $imagename = $currentId . "." . $filenew->getClientOriginalExtension();
                $filenew->move('uploads/specialoffer', $imagename);
                $image_data = ['specialofferphoto' => $imagename];
                $image_update = $this->model->doEdit($image_data, $table, $currentId);
            }
            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Special Offer " . $currentId . " is added";
            $this->model->activity_log($data);
            //activity log end

            return redirect('specialoffer/list')->with('success', 'Record added successfully');
        }
        return back()->with('error', 'Failed to add the record');
    }

    public function edit(Request $request)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $specialoffer = Specialoffer::select('specialoffer.*')
                ->where('specialoffer.code', $code)
                ->first();
            if (!empty($specialoffer)) {
                $data['pops'] = Softdrinks::where("isActive", 1)->where("type", "pop")->get();
                $data['bottle'] = Softdrinks::where("isActive", 1)->where("type", "bottle")->get();
                $data['specialofferline'] = DB::table("specialofferlineentries")
                    ->join('sidemaster', 'sidemaster.code', "=", "specialofferlineentries.sidemasterCode")
                    ->join('sidelineentries', 'sidelineentries.code', "=", "specialofferlineentries.sidelineentries")
                    ->select('specialofferlineentries.*', "sidemaster.sidename", "sidemaster.type", "sidelineentries.size", "sidelineentries.price")
                    ->where('specialofferlineentries.isActive', 1)
                    ->where('specialofferlineentries.isDelete', 0)
                    ->where('specialofferlineentries.specialOfferCode', $code)
                    ->orderby('specialofferlineentries.id', 'ASC')
                    ->get();
                $data['pizzaPrices'] = DB::table('pizza_prices')->where('isActive', 1)->orderBy('order_column', 'ASC')->get();
                $data['queryresult'] = $specialoffer;
                return view('specialoffer.edit', $data);
            }
        } else {
            return view('noright');
        }
    }

    public function update(Request $r)
    {
        $table = "specialoffer";
        $currentdate = Carbon::now();
        $code = $r->code;
        $ip = $_SERVER['REMOTE_ADDR'];

        $offer = DB::table($table)->where('code', $code)->first();

        $rules = [
            'name' => 'required|min:3|max:150',
            'subtitle' => 'nullable|min:3|max:150',
            'dealType' => 'required',
            'description' => 'nullable|min:2|max:300',
            'specialofferphoto' => 'nullable|image|mimes:jpg,png,jpeg',
            //'noofToppings' => 'required',
            'noofDips' => 'required',
            'noofSides' => 'required',
            'noofPizza' => 'required',
            'type' => 'nullable',
            'pops' => 'nullable',
            'bottle' => 'nullable',
            'showOnClient' => 'nullable', // Developer: Shreyas Mahamuni, Wokring Date: 21-12-2024
            'limited_offer' => 'nullable',
            'start_date' => [
                'nullable',
                'required_if:limited_offer,1',
                'date',
                function ($attribute, $value, $fail) use ($offer, $r) {
                    // Convert the input value and today's date to Carbon instances
                    $startDate = Carbon::parse($value);
                    $today = Carbon::now();
                    $offerStartDate = $offer->start_date;
                    // Check if the start_date is in the past
                    if ($r->limited_offer == 1 && $startDate->lt($today) && $startDate->format('Y-m-d H:i:s') !== $offerStartDate) {
                        $fail("The $attribute must be a future date-time unless it matches the original start date-time.");
                    }
                },
            ],

            'end_date' => [
                'nullable',
                'required_if:limited_offer,1',
                'date',
                'after:start_date', // Ensure end_date is after or equal to start_date
                function ($attribute, $value, $fail) {
                    // Convert the input value and today's date to Carbon instances
                    $endDate = Carbon::parse($value);
                    $today = Carbon::now();

                    // Ensure end_date is not in the past
                    if ($endDate->lt($today)) {
                        $fail("The $attribute must be a future date-time or match today.");
                    }
                },
            ],

            'pizzaPrice.*.price'    => 'required|gte:0'
        ];
        $messages = [
            'name.required' => 'Special offer name is required',
            'name.min' => 'Minimum of 3 characters are required.',
            'name.max' => 'Max characters exceeded.',
            'subtitle.min' => 'Minimum of 3 characters are required.',
            'subtitle.max' => 'Max characters exceeded.',
            'description.min' => 'Minimum of 2 characters are required.',
            'description.max' => 'Max characters exceeded.',
            //'noofToppings.required' => 'Number of toppings is required',
            'noofDips.required' => 'Number of dips is required',
            'noofSides.required' => 'Number of sides is required',
            'noofPizza.required' => 'Number of pizza is required.',
            'dealType.required' => 'Deal Type is required.',
            'start_date.required_if' => 'Offer start date is required',
            'end_date.required_if' => 'Offer end date is required',
            'pizzaPrice.*.price.required'    => 'Pizza Price are required',
            'pizzaPrice.*.price.gte'    => 'Pizza Prices should be greater than or equal to 0 (zero).'
        ];
        $this->validate($r, $rules, $messages);

        $pizzaPrices = [];

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

        foreach ($r->pizzaPrice as $item) {
            $pizzaPrices[] = [
                'size' => $item['size'],
                'price' => $item['price'],
                'shortcode' => $item['shortcode'],
            ];
        }

        $data = [
            'name' => ucwords(strtolower($r->name)),
            'subtitle' => $r->subtitle,
            'dealType' => $r->dealType,
            'noofToppings' => $r->noofToppings ?? 0,
            'noofDips' => $r->noofDips,
            'noofSides' => $r->noofSides,
            'noofPizza' => $r->noofPizza,
            'price' => $r->price,
            'extraLargePrice' => $r->extraLargePrice,
            'pops' => $r->pops,
            'bottle' => $r->bottle,
            'type' => json_encode($r->type),
            'description' => $r->description,
            'showOnClient' => $r->showOnClient == null ? 0 : $r->showOnClient,   // Developer: Shreyas Mahamuni, Wokring Date: 21-12-2024
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code,
            'limited_offer' => $r->limited_offer ?? 0,
            'start_date' => $r->start_date ? date('Y-m-d H:i:00', strtotime($r->start_date)) : null,
            'end_date' => $r->end_date ? date('Y-m-d H:i:00', strtotime($r->end_date)) : null,
            'pizza_prices' => stripslashes(json_encode($pizzaPrices))
        ];

        $result = $this->model->doEdit($data, $table, $code);
        if ($filenew = $r->file('specialofferphoto')) {
            $imagename = $code . "." . $filenew->getClientOriginalExtension();
            $filenew->move('uploads/specialoffer', $imagename);
            $image_data = ['specialofferphoto' => $imagename];
            $image_update = $this->model->doEdit($image_data, $table, $code);
        }
        if ($result == true || $image_update == true) {
            if ($r->has('sides')) {
                $sides = $r->sides;
                $size = $r->size;
                $rowCodes = $r->rowCode;
                for ($i = 0; $i < count($sides); $i++) {
                    if ($r->noofSides == 0) {
                        $subdata = [
                            'specialOfferCode' => $code,
                            'sidemasterCode' => $sides[$i],
                            'sidelineentries' => $size[$i],
                            'isActive' => '0',
                            'isDelete' => '1',
                        ];
                    } else {
                        $subdata = [
                            'specialOfferCode' => $code,
                            'sidemasterCode' => $sides[$i],
                            'sidelineentries' => $size[$i],
                            'isActive' => '1',
                            'isDelete' => '0',
                        ];
                    }
                    if ($rowCodes[$i] != "-") {
                        $subdata['editDate'] = date('Y-m-d H:i:s');
                        $subdata['editIP'] = $ip;
                        $subdata['editID'] = Auth::guard('admin')->user()->code;
                        $this->model->doEdit($subdata, "specialofferlineentries", $rowCodes[$i]);

                        //activity log start
                        $data = $currentdate->toDateTimeString() . "	" . $ip . "	" . Auth::guard('admin')->user()->code .    "	Side Offer Line Entries " . $rowCodes[$i] . " is updated";
                        $this->model->activity_log($data);
                        //activity log end

                    } else {
                        $subdata['addDate'] = date('Y-m-d H:i:s');
                        $subdata['addIP'] = $ip;
                        $subdata['addID'] = Auth::guard('admin')->user()->code;
                        $result = $this->model->addNew($subdata, 'specialofferlineentries', 'SOL');

                        //activity log start
                        $data = $currentdate->toDateTimeString() . "	" . $ip . "	" . Auth::guard('admin')->user()->code .    "	Side Offer Line Entries " . $code . " is added";
                        $this->model->activity_log($data);
                        //activity log end
                    }
                }
            }

            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	 Special Offer " . $code . " is updated.";
            $this->model->activity_log($data);
            //activity log end
            return redirect('specialoffer/list')->with('success', 'Special Offer updated successfully');
        } else {
            return back()->with('error', 'Failed to update the special offer');
        }
    }

    public function deleteSpecialOfferLine(Request $r)
    {
        $currentdate = Carbon::now();
        $itemCode = $r->itemCode;

        $ip = $_SERVER['REMOTE_ADDR'];
        $today = date('Y-m-d H:i:s');
        $table = 'specialofferlineentries';
        $data = ['isActive' => 0, 'isDelete' => 1, 'deleteIP' => $ip, 'deleteID' => Auth::guard('admin')->user()->code, 'deleteDate' => $today];
        $result = $this->model->doEditWithField($data, $table, 'code', $itemCode);
        if ($result == true) {
            //activity log start
            $datastring = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	special offer line entries " . $itemCode . " is deleted.";
            $this->model->activity_log($datastring);
            //activity log end
            return response()->json(["status" => "success"], 200);
        } else {
            return response()->json(["status" => "fail"], 200);
        }
    }

    public function deleteAllSpecialOfferLine(Request $r)
    {
        $currentdate = Carbon::now();
        $specialOfferCode = $r->specialOfferCode;
        $ip = $_SERVER['REMOTE_ADDR'];
        $today = date('Y-m-d H:i:s');
        $table = 'specialofferlineentries';
        $data = ['isActive' => 0, 'isDelete' => 1, 'deleteIP' => $ip, 'deleteID' => Auth::guard('admin')->user()->code, 'deleteDate' => $today];
        $result = $this->model->doEditWithField($data, $table, 'specialOfferCode', $specialOfferCode);
        if ($result == true) {
            //activity log start
            $datastring = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	special offer line entries " . $specialOfferCode . " is deleted.";
            $this->model->activity_log($datastring);
            //activity log end
            return response()->json(["status" => "success"], 200);
        } else {
            return response()->json(["status" => "fail"], 200);
        }
    }

    public function deleteImage(Request $r)
    {
        $imgNm = $r->value;
        $code = $r->code;
        $data = array(
            'specialofferphoto' => '',
        );
        if (!empty($data)) {
            unlink('uploads/specialoffer/' . $imgNm);
            echo $resultData = $this->model->doEdit($data, 'specialoffer', $code);
        } else {
            echo 'false';
        }
    }

    public function delete(Request $r)
    {
        $currentdate = Carbon::now();
        $code = $r->code;
        $ip = $_SERVER['REMOTE_ADDR'];
        $today = date('Y-m-d H:i:s');
        $table = 'specialoffer';
        $data = ['isActive' => 0, 'isDelete' => 1, 'deleteIP' => $ip, 'deleteID' => Auth::guard('admin')->user()->code, 'deleteDate' => $today];

        //activity log start
        $datastring = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Special Offer " . $code . "  is deleted.";
        $this->model->activity_log($datastring);
        //activity log end

        $result = $this->model->doEditWithField($data, $table, 'code', $code);

        if ($result == true) {
            return response()->json(["status" => "success"], 200);
        } else {
            return response()->json(["status" => "fail"], 200);
        }
    }

    public function view(Request $request)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $specialoffer = Specialoffer::select('specialoffer.*', 'sd1.softdrinks as pops', 'sd2.softdrinks as bottle')
                ->join('softdrinks as sd1', "sd1.code", "=", "specialoffer.pops", "left")
                ->join('softdrinks as sd2', "sd2.code", "=", "specialoffer.bottle", "left")
                ->where('specialoffer.code', $code)
                ->first();
            $data['specialofferline'] = DB::table("specialofferlineentries")
                ->join('sidemaster', 'sidemaster.code', "=", "specialofferlineentries.sidemasterCode")
                ->join('sidelineentries', 'sidelineentries.code', "=", "specialofferlineentries.sidelineentries")
                ->select('specialofferlineentries.*', "sidemaster.sidename", "sidelineentries.size", "sidelineentries.price")
                ->where('specialofferlineentries.isActive', 1)
                ->where('specialofferlineentries.isDelete', 0)
                ->where('specialofferlineentries.specialOfferCode', $code)
                ->orderby('specialofferlineentries.id', 'ASC')
                ->get();
            if (!empty($specialoffer)) {
                $data['queryresult'] = $specialoffer;
                return view('specialoffer.view', $data);
            }
        } else {
            return view('noright');
        }
    }
}
