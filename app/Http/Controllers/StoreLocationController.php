<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\Province;
use App\Models\Storelocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;
use Illuminate\Support\Facades\Log;
use App\Services\DoorDashService;
use App\Models\Business;

class StoreLocationController extends Controller
{
    private $role, $rights;
    protected DoorDashService $doorDashService;
    public function __construct(GlobalModel $model, DoorDashService $doorDashService)
    {
        $this->model = $model;
        $this->doorDashService = $doorDashService;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('2.2', $this->role);
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
            return view('storelocation.list', $data);
        } else {
            return view('noright');
        }
    }

    public function getStoreLocation(Request $r)
    {
        $html = [];
        $search = $r->search;
        $role = $r->role;
        $like = array('storelocation.storeLocation' => $search);
        $condition = array('storelocation.isDelete' => array('=', 0));
        $orderBy = array('storelocation' . '.id' => 'DESC');
        if ($role === 'R_4') {
            $condition = array('storelocation.isDelete' => array('=', 0), 'storelocation.isMain' => array('=', 1));
        }
        $result = $this->model->selectQuery('storelocation.*', 'storelocation', array(), $condition, $orderBy, $like, '', '');
        if ($result) {
            foreach ($result as $item) {
                $html[] = array('id' => $item->code, 'text' => $item->storeLocation, 'isMain' => $item->isMain);
            }
        }
        echo  json_encode($html);
    }

    public function getStoreLocationList(Request $req)
    {
        $storelocation = $req->storelocation;
        $search = $req->input('search.value');
        $tableName = "storelocation";
        $orderColumns = array("storelocation.*", "province_tax_rates.province_state", "province_tax_rates.tax_percent");
        $condition = array('storelocation.isDelete' => array('=', 0), 'storelocation.code' => array('=', $storelocation));
        $orderBy = array('storelocation' . '.id' => 'DESC');
        $join = array('province_tax_rates' => ['storelocation.tax_province_id', 'province_tax_rates.id']);
        $like = array('storelocation.storeLocation' => $search, 'storelocation.city' => $search);
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
                    $actions .= '<a class="dropdown-item" href="' . url("storelocation/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("storelocation/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights != '' && $this->rights['delete'] == 1) {
                    $actions .= '<a style="cursor:pointer;"class="dropdown-item delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash mr-2" href></i> Delete</a>';
                }

                $actions .= '</div></div>';

                $tax = '<div>
                    <div>' . $row->province_state . '</div>
                    <div><strong>' . $row->tax_percent . '%</strong></div>
                </div>';

                $data[] = array(
                    $srno,
                    $actions,
                    $row->storeLocation,
                    $row->city,
                    $tax,
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

    public function add()
    {
        if ($this->rights != '' && $this->rights['insert'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $data['prevCities'] = Storelocation::select('city')->distinct('city')->where('isActive', 1)->where('isDelete', 0)->get();
            $data['provinces'] = Province::get();
            return view('storelocation.add', $data);
        } else {
            return view('backend.noright');
        }
    }

    public function store(Request $r)
    {
        $table = "storelocation";
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'name' => 'required|min:3|max:150',
            'latitude' => 'required',
            'longitude' => 'required',
            'storeAddress' => 'required|min:20|max:300',
            'city' => 'required|min:3|max:50',
            'tax_province_id' => 'required',
        ];
        $messages = [
            'name.required' => 'Store Location name is required.',
            'name.min' => 'Minimum of 3 characters are required.',
            'name.max' => 'Max characters exceeded.',
            'latitude.required' => 'Latitude is required.',
            'longitude.required' => 'Longitude is required',
            'storeAddress.required' => 'Store Location name is required.',
            'storeAddress.min' => 'Minimum of 20 characters are required.',
            'storeAddress.max' => 'Max characters exceeded.',
            'city.required' => 'City is required',
            'city.min' => 'Minimum of 3 characters are required.',
            'city.max' => 'Maximum of 50 characters are allowed.',
            'tax_province_id.required' => "Provice - Tax is not selected"
        ];
        $this->validate($r, $rules, $messages);

        // Parse the times
        $startTimeWD = Carbon::createFromFormat('H:i', $r->input('wd_start_time'));
        $endTimeWD = Carbon::createFromFormat('H:i', $r->input('wd_end_time'));
        $startTimeWE = Carbon::createFromFormat('H:i', $r->input('we_start_time'));
        $endTimeWE = Carbon::createFromFormat('H:i', $r->input('we_end_time'));

        // Check if start time and end time are the same for weekdays
        if ($endTimeWD->eq($startTimeWD)) {
            return back()->withInput($r->only([
                'name',
                'storeAddress',
                'wd_start_time',
                'wd_end_time',
                'we_start_time',
                'we_end_time',
            ]))->with('error', 'Weekday start time and end time cannot be the same');
        }

        // Check if start time and end time are the same for weekends
        if ($endTimeWE->eq($startTimeWE)) {
            return back()->withInput($r->only([
                'name',
                'storeAddress',
                'wd_start_time',
                'wd_end_time',
                'we_start_time',
                'we_end_time',
            ]))->with('error', 'Weekend start time and end time cannot be the same');
        }

        $data = [
            'storeLocation' => ucwords(strtolower($r->name)),
            'city' => ucwords(strtolower($r->city)),
            'storeAddress' => $r->storeAddress,
            'latitude' => $r->latitude,
            'longitude' => $r->longitude,
            'weekdays_start_time' => $r->wd_start_time,
            'weekdays_end_time' => $r->wd_end_time,
            'weekend_start_time' => $r->we_start_time,
            'weekend_end_time' => $r->we_end_time,
            'isMain' => 0,
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'addIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'addID' => Auth::guard('admin')->user()->code,
            'tax_province_id' => $r->tax_province_id,
            'timezone' => $r->timezone,
            'pickup_number' => $r->pickupNumber
        ];

        $currentId = $this->model->addNew($data, $table, 'STR');
        if ($currentId) {


            // Get the first business from the table
            $business = Business::orderBy('id', 'asc')->first();
            if (!$business) {
                return back()->withInput()->with('error', 'No business found. Please create a business first.');
            }

            // Prepare payload for DoorDash API
            $apiParams = [
                'external_business_id' => $business->external_business_id,
                'external_store_id' => $currentId,
                'name' => $r->name,
                'phone_number' => $r->pickupNumber,
                'address' => $r->storeAddress
            ];

            // Call DoorDash API to create store
            $result = $this->doorDashService->createStore($apiParams);

            DB::table($table)
                ->where('code', $currentId)
                ->update([
                    'doordash_response' => json_encode($result),
                ]);

            if (!isset($result['success']) || $result['success'] !== true) {
                return back()->withInput()->with('error', 'Failed to create store in DoorDash: ' . ($result['message'] ?? 'Unknown error'));
            }


            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Store Location " . $currentId . " is added";
            $this->model->activity_log($data);
            //activity log end

            return redirect('storelocation/list')->with('success', 'Record added successfully');
        }
        return back()->with('error', 'Failed to add the record');
    }

    public function update(Request $r)
    {
        $table = "storelocation";
        $currentdate = Carbon::now();
        $code = $r->code;
        $ip = $_SERVER['REMOTE_ADDR'];

        $rules = [
            'name' => 'required|min:3|max:150',
            'latitude' => 'required',
            'longitude' => 'required',
            'storeAddress' => 'required|min:20|max:300',
            'city' => 'required|min:3|max:50',
            'tax_province_id' => 'required'
        ];

        $messages = [
            'name.required' => 'Store Location name is required',
            'name.min' => 'Minimum of 3 characters are required.',
            'name.max' => 'Max characters exceeded.',
            'latitude.required' => 'Latitude is required.',
            'longitude.required' => 'Longitude is required.',
            'storeAddress.required' => 'Store Location name is required.',
            'storeAddress.min' => 'Minimum of 20 characters are required.',
            'storeAddress.max' => 'Max characters exceeded.',
            'city.required' => 'City is required',
            'city.min' => 'Minimum of 3 characters are required.',
            'city.max' => 'Maximum of 50 characters are allowed.',
            'tax_province_id.required' => 'Province - Tax is not selected'
        ];

        $this->validate($r, $rules, $messages);

        // Parse the times
        $startTimeWD = Carbon::createFromFormat('H:i', $r->input('wd_start_time'));
        $endTimeWD = Carbon::createFromFormat('H:i', $r->input('wd_end_time'));
        $startTimeWE = Carbon::createFromFormat('H:i', $r->input('we_start_time'));
        $endTimeWE = Carbon::createFromFormat('H:i', $r->input('we_end_time'));

        // Check if start time and end time are the same for weekdays
        if ($endTimeWD->eq($startTimeWD)) {
            return back()->withInput($r->only([
                'name',
                'storeAddress',
                'wd_start_time',
                'wd_end_time',
                'we_start_time',
                'we_end_time',
            ]))->with('error', 'Weekday start time and end time cannot be the same');
        }

        // Check if start time and end time are the same for weekends
        if ($endTimeWE->eq($startTimeWE)) {
            return back()->withInput($r->only([
                'name',
                'storeAddress',
                'wd_start_time',
                'wd_end_time',
                'we_start_time',
                'we_end_time',
            ]))->with('error', 'Weekend start time and end time cannot be the same');
        }

        $data = [
            'storeLocation' => ucwords(strtolower($r->name)),
            'city' => ucwords(strtolower($r->city)),
            'storeAddress' => $r->storeAddress,
            'latitude' => $r->latitude,
            'longitude' => $r->longitude,
            'weekdays_start_time' => $r->wd_start_time,
            'weekdays_end_time' => $r->wd_end_time,
            'weekend_start_time' => $r->we_start_time,
            'weekend_end_time' => $r->we_end_time,
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code,
            'tax_province_id' => $r->tax_province_id,
            'timezone' => $r->timezone,
            'pickup_number' => $r->pickupNumber
        ];

        $result = $this->model->doEdit($data, $table, $code);

        if ($result == true) {
            /* ================== DOORDASH STORE CREATE OR UPDATE ================== */

            // Get the first business from the table
            $business = Business::orderBy('id', 'asc')->first();

            if ($business) {
                // Prepare payload for DoorDash API
                $apiParams = [
                    'external_business_id' => $business->external_business_id,
                    'external_store_id' => $code,
                    'name' => $r->name,
                    'phone_number' => $r->pickupNumber,
                    'address' => $r->storeAddress
                ];

                // Check if store exists in DoorDash
                Log::info('Checking if DoorDash store exists', [
                    'external_business_id' => $business->external_business_id,
                    'external_store_id' => $code
                ]);

                $getStoreResult = $this->doorDashService->getStore(
                    $business->external_business_id,
                    $code
                );

                $ddResponse = null;

                // If store exists, update it
                if (isset($getStoreResult['success']) && $getStoreResult['success'] === true) {
                    Log::info('Store exists in DoorDash, updating...', [
                        'external_store_id' => $code
                    ]);

                    $ddResponse = $this->doorDashService->updateStore($apiParams);

                    Log::info('DoorDash Store Update Result', [
                        'store_code' => $code,
                        'response' => $ddResponse
                    ]);
                } else {
                    // Store doesn't exist, create it
                    Log::info('Store does not exist in DoorDash, creating...', [
                        'external_store_id' => $code
                    ]);

                    $ddResponse = $this->doorDashService->createStore($apiParams);

                    Log::info('DoorDash Store Create Result', [
                        'store_code' => $code,
                        'response' => $ddResponse
                    ]);
                }

                // Save DoorDash response to database (optional - if you have this column)
                try {
                    DB::table($table)
                        ->where('code', $code)
                        ->update([
                            'doordash_response' => json_encode($ddResponse)
                        ]);
                } catch (\Exception $e) {
                    Log::warning('Could not save DoorDash response to database', [
                        'error' => $e->getMessage()
                    ]);
                }

                // Log if DoorDash sync failed (but don't block the update)
                if (!isset($ddResponse['success']) || $ddResponse['success'] !== true) {
                    $errorMessage = $ddResponse['message'] ?? $ddResponse['error'] ?? 'Unknown error';

                    Log::error('DoorDash Store sync failed', [
                        'external_store_id' => $code,
                        'error' => $errorMessage,
                        'full_response' => $ddResponse
                    ]);
                }
            } else {
                Log::warning('No business found for DoorDash sync', [
                    'store_code' => $code
                ]);
            }

            //activity log start
            $data = $currentdate->toDateTimeString() . "	" . $ip . "	" . Auth::guard('admin')->user()->code . "	 Store Location " . $code . " is updated.";
            $this->model->activity_log($data);
            //activity log end

            return redirect('storelocation/list')->with('success', 'Store Location updated successfully');
        } else {
            return back()->with('error', 'Failed to update the store location');
        }
    }

    public function edit(Request $request)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $storelocation = Storelocation::join("province_tax_rates", "province_tax_rates.id", "=", "storelocation.tax_province_id")
                ->select('storelocation.*')
                ->where('storelocation.code', $code)
                ->first();
            if (!empty($storelocation)) {
                $data['queryresult'] = $storelocation;
                $data['prevCities'] = Storelocation::select('city')->distinct('city')->where('isActive', 1)->where('isDelete', 0)->get();
                $data['provinces'] = Province::get();
                return view('storelocation.edit', $data);
            }
        } else {
            return view('noright');
        }
    }

    public function delete(Request $r)
    {
        $currentdate = Carbon::now();
        $code = $r->code;

        $ip = $_SERVER['REMOTE_ADDR'];
        $today = date('Y-m-d H:i:s');
        $table = 'storelocation';
        $data = [
            'isActive' => 0,
            'isDelete' => 1,
            'deleteIP' => $ip,
            'deleteID' => Auth::guard('admin')->user()->code,
            'deleteDate' => $today
        ];

        $isMain = DB::table('storelocation')->where('code', $code)->where('isMain', 1)->first();

        if ($isMain) {
            return response()->json(["status" => 300, 'message' => "Primary store location cannot be deleted."], 200);
        } else {
            // Activity log
            $datastring = $currentdate->toDateTimeString() . "\t" . $ip . "\t" . Auth::guard('admin')->user()->code . "\tStore Location " . $code . " is deleted.";
            $this->model->activity_log($datastring);

            // Delete the store location record
            $result = $this->model->doEditWithField($data, $table, 'code', $code);

            if ($result == true) {
                return response()->json(["status" => 200, 'message' => "Success."], 200); // If deletion is successful, return success
            } else {
                return response()->json(["status" => 300, 'message' => "Failed."], 200); // If deletion fails, return failure
            }
        }
    }

    public function view(Request $request)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $code = $request->code;
            $storelocation = Storelocation::join("province_tax_rates", "province_tax_rates.id", "=", "storelocation.tax_province_id")
                ->select('storelocation.*', 'province_tax_rates.province_state', 'province_tax_rates.tax_percent')
                ->where('storelocation.code', $code)
                ->first();
            if (!empty($storelocation)) {
                $data['queryresult'] = $storelocation;
                $data['prevCities'] = Storelocation::select('city')->distinct('city')->where('isActive', 1)->where('isDelete', 0)->get();
                $data['provinces'] = Province::get();
                return view('storelocation.view', $data);
            }
        } else {
            return view('noright');
        }
    }
}
