<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DynamicSlider;
use App\Models\DynamicSliderLineentries;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\Cheese;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;


class DynamicSliderController  extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('2.5', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    // Developer: Shreyas Mahamuni, Working Date: 08-05-2024
    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['insertRights'] = $this->rights['insert'];
            return view('dynamic-sliders.list', $data);
        } else {
            return view('noright');
        }
    }
    // Developer: Shreyas Mahamuni, Working Date: 08-05-2024
    public function getDynamicSliderList(Request $req)
    {
        $search = $req->input('search.value');
        $tableName = "dynamic_sliders";
        $orderColumns = array("dynamic_sliders.*");
        $condition = array();
        $orderBy = array('dynamic_sliders' . '.id' => 'DESC');
        $join = array();
        $like = array('dynamic_sliders.title' => $search);
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
                    $actions .= '<a class="dropdown-item" href="' . url("dynamic-sliders/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("dynamic-sliders/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights != '' && $this->rights['delete'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("dynamic-sliders/delete/" . $row->code) . '"><i class="fas fa-trash mr-2"></i> Delete</a>';
                }

                $actions .= '</div>
               </div>';
                $data[] = array(
                    $srno,
                    $actions,
                    $row->title,
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

    // Developer: Shreyas Mahamuni, Working Date: 08-05-2024
    public function add()
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            return view('dynamic-sliders.add', $data);
        } else {
            return view('noright');
        }
    }

    // Developer: Shreyas Mahamuni, Working Date: 08-05-2024
    public function store(Request $r)
    {
        $table = "dynamic_sliders";
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'title' => [
                'required',
                'min:2',
            ],
            'background_image' => 'required|image|mimes:jpg,jpeg,png',
            'background_image_md' => 'required|image|mimes:jpg,jpeg,png',
            'background_image_sm' => 'required|image|mimes:jpg,jpeg,png'
        ];
        $messages = [
            'title.required' => 'Title is required',
            'title.min' => 'Minimum of 2 characters are required.',
            'background_image.image' => 'Slider image must be an image file',
            'background_image.mimes' => 'Slider image must be a file of type: jpg, jpeg, png',
            'background_image_md.image' => 'Slider (medium) image must be an image file',
            'background_image_md.mimes' => 'Slider (medium) image must be a file of type: jpg, jpeg, png',
            'background_image_sm.image' => 'Slider (small) image must be an image file',
            'background_image_sm.mimes' => 'Slider (small) image must be a file of type: jpg, jpeg, png',
        ];
        $this->validate($r, $rules, $messages);

        $data = [
            'title' => ucwords(strtolower($r->title)),
            'isActive' => 1,
            'subTitle' => $r->subTitle,
            'created_at' => $currentdate->toDateTimeString(),
        ];
        $currentId = $this->model->addNew($data, $table, 'SLI');
        if ($currentId) {
            $image_data = [];
            /*if (count($r->store_address) > 0) {
                for ($i = 0; $i < count($r->store_address); $i++) {
                    $lineentries_data = [
                        "slider_code" => $currentId,
                        "store_address" => $r->store_address[$i],
                        'created_at' => $currentdate->toDateTimeString(),
                    ];
                    $lineentriesCode = $this->model->addNew($lineentries_data, "dynamic_slider_lineentries", 'SLIL');
                    $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Dynamic Slider Lineentries " . $currentId . " " . $lineentriesCode . " is added.";
                    $this->model->activity_log($data);
                }
                //activity log start
                $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Dynamic Slider " . $currentId . " is added.";
                $this->model->activity_log($data);
                //activity log end
            }*/
            if ($filenew = $r->file('background_image')) {
                $imagename = $currentId . "-lg." . $filenew->getClientOriginalExtension();
                $filenew->move('uploads/slider-background', $imagename);
                $image_data['background_image'] = $imagename;
            }

            if ($filenew = $r->file('background_image_md')) {
                $imagename = $currentId . "-md." . $filenew->getClientOriginalExtension();
                $filenew->move('uploads/slider-background', $imagename);
                $image_data['background_image_md'] = $imagename;
            }

            if ($filenew = $r->file('background_image_sm')) {
                $imagename = $currentId . "-sm." . $filenew->getClientOriginalExtension();
                $filenew->move('uploads/slider-background', $imagename);
                $image_data['background_image_sm'] = $imagename;
            }
            if (!empty($image_data)) {
                $this->model->doEdit($image_data, $table, $currentId);
            }
            return redirect('dynamic-sliders/list')->with('success', 'Slider updated successfully');
        } else {
            return back()->with('error', 'Failed to update the slider');
        }
    }

    // Developer: Shreyas Mahamuni, Working Date: 08-05-2024
    public function edit(Request $r)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $r->code;
            $data['queryresult'] = DynamicSlider::where('code', $code)->first();
            $data['lineentries'] = DynamicSliderLineentries::where('slider_code', $code)->get();
            return view('dynamic-sliders.edit', $data);
        } else {
            return view('noright');
        }
    }

    // Developer: Shreyas Mahamuni, Working Date: 08-05-2024, 09-05-2024
    public function update(Request $r)
    {
        $table = "dynamic_sliders";
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'code' => 'required',
            'title' => [
                'required',
                'min:2',
            ],
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png',
            'background_image_md' => 'nullable|image|mimes:jpg,jpeg,png',
            'background_image_sm' => 'nullable|image|mimes:jpg,jpeg,png'
        ];
        $messages = [
            'code.required' => 'Code is required.',
            'title.required' => 'Title is required',
            'title.min' => 'Minimum of 2 characters are required.',
            'background_image.image' => 'Background image must be an image file',
            'background_image.mimes' => 'Background image must be a file of type: jpg, jpeg, png',

            'background_image_md.image' => 'Background (medium) image must be an image file',
            'background_image_md.mimes' => 'Background (medium) image must be a file of type: jpg, jpeg, png',

            'background_image_sm.image' => 'Background (small) image must be an image file',
            'background_image+sm.mimes' => 'Background (small) image must be a file of type: jpg, jpeg, png',

        ];
        $this->validate($r, $rules, $messages);
        $code = $r->code;
        $data = [
            'title' => ucwords(strtolower($r->title)),
            'isActive' => 1,
            //'subTitle' => $r->subTitle,
            'updated_at' => $currentdate->toDateTimeString(),
        ];
        //if (isset($r->store_address)) {
        if ($filenew = $r->file('background_image')) {
            $imagename = $code . "-lg." . $filenew->getClientOriginalExtension();
            $filenew->move('uploads/slider-background', $imagename);
            $data['background_image'] = $imagename;
        }

        if ($filenew = $r->file('background_image_md')) {
            $imagename = $code . "-md." . $filenew->getClientOriginalExtension();
            $filenew->move('uploads/slider-background', $imagename);
            $data['background_image_md'] = $imagename;
        }

        if ($filenew = $r->file('background_image_sm')) {
            $imagename = $code . "-sm." . $filenew->getClientOriginalExtension();
            $filenew->move('uploads/slider-background', $imagename);
            $data['background_image_sm'] = $imagename;
        }

        $result = $this->model->doEdit($data, $table, $code);
        if ($result == true) {
            /*if (count($r->store_address) > 0) {
                    for ($i = 0; $i < count($r->store_address); $i++) {
                        if ($r->addr_code[$i] == "##NA") {
                            $lineentries_data = [
                                "slider_code" => $code,
                                "store_address" => $r->store_address[$i],
                                'created_at' => $currentdate->toDateTimeString(),
                            ];
                            $lineentriesCode = $this->model->addNew($lineentries_data, "dynamic_slider_lineentries", 'SLIL');
                            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Dynamic Slider Lineentries " . $code . " " . $lineentriesCode . " is added.";
                            $this->model->activity_log($data);
                        } else {
                            $lineentries_data = [
                                "store_address" => $r->store_address[$i],
                                'updated_at' => $currentdate->toDateTimeString(),
                            ];
                            $lineentriesCode = $this->model->doEdit($lineentries_data, "dynamic_slider_lineentries", $r->addr_code[$i]);
                            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Dynamic Slider Lineentries " . $code . " " . $lineentriesCode . " is updated.";
                            $this->model->activity_log($data);
                        }
                    }
                    //activity log start
                    $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Dynamic Slider " . $code . " is updated.";
                    $this->model->activity_log($data);
                    //activity log end
                }*/

            return redirect('dynamic-sliders/list')->with('success', 'Slider updated successfully');
        } else {
            return back()->with('error', 'Failed to update the slider');
        }
        /*} else {
            return back()->with('error', "Can't update, At least 1 store address is required.");
        }*/
    }

    // Developer:Shreyas Mahamuni, Working Date: 04-10-2024
    public function deleteImage(Request $r)
    {
        $table = "dynamic_sliders";
        $imgNm = $r->value;
        $code = $r->code;
        $size = $r->size;
        if ($size == 'lg') {
            $data = array(
                'background_image' => '',
            );
        } else if ($size == 'md') {
            $data = array(
                'background_image_md' => '',
            );
        } else {
            $data = array(
                'background_image_sm' => '',
            );
        }
        if (!empty($data)) {
            unlink('uploads/slider-background/' . $imgNm);
            echo $resultData = $this->model->doEdit($data, $table, $code);
        } else {
            echo 'false';
        }
    }


    // Developer: Shreyas Mahamuni, Working Date: 09-05-2024
    public function deleteLineentries($code = "")
    {
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($code != "") {
            DynamicSliderLineentries::where('code', $code)->delete();
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Dynamic Slider Linenetries " . $code . " is deleted.";
            $this->model->activity_log($data);

            return response()->json(['message' => 'Record deleted successfully.', "status" => 200], 200);
        }
        return response()->json(['message' => 'Failed to delete.', "status" => 400], 400);
    }

    // Developer: Shreyas Mahamuni, Working Date: 09-05-2024
    public function view(Request $r)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $r->code;
            $data['queryresult'] = DynamicSlider::where('code', $code)->first();
            $data['lineentries'] = DynamicSliderLineentries::where('slider_code', $code)->get();
            return view('dynamic-sliders.view', $data);
        } else {
            return view('noright');
        }
    }

    // Developer: Shreyas Mahamuni, Working Date: 09-05-2024
    public function delete($code = "")
    {
        $currentDate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($code != "") {
            DynamicSlider::where('code', $code)->delete();
            $data = $currentDate->toDateTimeString() . " " . $ip . " " . Auth::guard('admin')->user()->code .  "Dynamic Slider " . $code . " is deleted.";
            $this->model->activity_log($data);

            return redirect('dynamic-sliders/list')->with('success', 'Record deleted successfully.');
        }
    }
}
