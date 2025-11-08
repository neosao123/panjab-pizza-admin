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


class SettingController extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('2.3', $this->role);
            if (!$this->rights && $this->rights == "") {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    public function index()
    {
        return view('setting.list');
    }

    public function getSettingList(Request $req)
    {
        $search = $req->input('search.value');
        $tableName = "settings";
        $orderColumns = array("settings.*");
        $condition = array('settings.isDelete' => array('=', 0));
        $orderBy = array('settings.id' => 'ASC');
        $join = array();
        $like = array('settings.settingName' => $search);
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
                    $actions .= '<a class="dropdown-item" href="' .  url("setting/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' .  url("setting/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                $actions .= '</div>
						</div>';
                $data[] = array( 
                    $actions,
                    $row->settingName,
                    $row->settingValue,
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

    public function edit($code)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $data['setting'] = $this->model->selectDataByCode('settings', $code);
            return view('setting.edit', $data);
        } else {
            return view('noright');
        }
    }
    public function view($code)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['setting'] = $this->model->selectDataByCode('settings', $code);
            return view('setting.view', $data);
        } else {
            return view('noright');
        }
    }


    public function update(Request $r)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $settingName =  ucwords($r->settingName);
        if ($r->has('code') && trim($r->code) != "") {
            $code = $r->code;
            $data = [
                'settingValue' => trim($r->settingValue),
                'editIP' => $ip,
                'editDate' => $currentdate->toDateTimeString(),
                'editID' => Auth::guard('admin')->user()->code
            ];
            $result = $this->model->doEdit($data, 'settings', $r->code);
            if ($result != false) {

                if($r->code=="STG_7") {
                    $countAs = $r->settingValue;
                    DB::table('toppings')->where('topping_type', '!=', 'regular')->update(['countAs' => $countAs]);
                }

                //activity log start
                $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Settings " . $code . " Setting name & value  " . ucwords($r->settingName) . "" .  trim($r->settingValue)  . " is updated";
                $this->model->activity_log($data);
                //activity log end
                return redirect('setting/list')->with('success', 'Setting updated successfully');
            }
            return back()->with('error', 'Failed to update setting');
        }
    }
}
