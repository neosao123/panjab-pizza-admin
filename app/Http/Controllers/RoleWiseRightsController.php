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
use App\Models\UserRole;

class RoleWiseRightsController extends Controller
{
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('2.1', $this->role);
            if (!$this->rights && $this->rights == "") {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $table = "rolesmaster";
            $data['roles'] = UserRole::select("rolesmaster.*")->where('isActive', 1)->get();
            return view('rolewiserights.list', $data);
        } else {
            return view('noright');
        }
    }

    public function getMenuList(Request $r)
    {
        $role = $r->role;
        $menufile = storage_path('app/public/rights/menu.json');
        $rightfile = storage_path('app/public/rights/' . $role . '.json');
        if (($menufile)) {
            $filecontents = file_get_contents($menufile);
            $menuJson = json_decode($filecontents, true);
            $filename = $rightfile;
            $rightJson = [];
            if (file_exists($filename)) {
                $rightscontents = file_get_contents($filename);
                $rightJson = json_decode($rightscontents, true);
            }
            $menuHtml = '<table class="table table-bordered" style="width:100%;overflow-y:scroll" id="rights_table">
			<thead>
				<tr>
					<th>Sr No</th>
					<th>Menu/Submenus</th>
					<th>All</th>
					<th>View</th>
					<th>Insert</th>
					<th>Update</th>
					<th>Delete</th>
					<th>Default Page</th>
				</tr>
			</thead>
			<tbody>';
            $i = 0;
            $subCheck = 0;
            foreach ($menuJson as $menu) {
                $i++;
                $menuHtml .= '<tr><td>' . $i . '</td><td colspan="7"><b>' . $menu['name'] . '</b><input type="checkbox" style="width:20px;height:20px;margin-left:8px;vertical-align: inherit;" onchange="checkAllSubcheck(' . count($menu['submenu']) . ',' . $i . ',' . $subCheck . ')" name="allsubcheck' . $i . '" id="allsubcheck' . $i . '"> <b>(All Submenus)</b></td></tr>';
                foreach ($menu['submenu'] as $sub) {
                    $subCheck++;
                    $checked = '';
                    $view = $delete = $insert = $update = $allcheck = '';
                    if (!empty($rightJson)) {
                        foreach ($rightJson as $rt) {
                            if ($rt['menu'] == $sub['id']) {
                                if ($rt['view'] == 1) $view = 'checked';
                                if ($rt['insert'] == 1) $insert = 'checked';
                                if ($rt['update'] == 1) $update = 'checked';
                                if ($rt['delete'] == 1) $delete = 'checked';
                                if ($rt['default'] == 1) $checked = 'checked';
                                if ($rt['view'] == 1 && $rt['insert'] == 1 && $rt['update'] == 1 && $rt['delete'] == 1) $allcheck = 'checked';
                            }
                        }
                    } else {
                        if ($subCheck == 1) $checked = 'checked';
                    }
                    $menuHtml .= '<tr id="row' . $subCheck . '" align="center"><td></td>
						<td>' . $sub['name'] . '<input type="hidden" id="menu' . $subCheck . '" name="menu' . $subCheck . '" value="' . $sub['id'] . '"></td>
						<td><input type="checkbox" style="width:22px;height:22px" onchange="checkAll(' . $subCheck . ')" name="allcheck' . $subCheck . '" ' . $allcheck . ' id="allcheck' . $subCheck . '"></td>
						<td><input type="checkbox" class ="cb-element' . $subCheck . '" onchange="validateAllCheck(' . $subCheck . ')" style="width:22px;height:22px" name="view' . $subCheck . '" ' . $view . ' id="view' . $subCheck . '"></td>
						<td><input type="checkbox" class ="cb-element' . $subCheck . '" onchange="validateAllCheck(' . $subCheck . ')" style="width:22px;height:22px" name="insert' . $subCheck . '" ' . $insert . ' id="insert' . $subCheck . '"></td>
						<td><input type="checkbox" class ="cb-element' . $subCheck . '" onchange="validateAllCheck(' . $subCheck . ')" style="width:22px;height:22px" name="update' . $subCheck . '" ' . $update . ' id="update' . $subCheck . '"></td>
						<td><input type="checkbox" class ="cb-element' . $subCheck . '" onchange="validateAllCheck(' . $subCheck . ')" style="width:22px;height:22px" name="delete' . $subCheck . '" ' . $delete . ' id="delete' . $subCheck . '"></td>
						<td><input type="radio" style="width:22px;height:22px" name="default" id="default' . $subCheck . '" ' . $checked . '></td>
					</tr>';
                }
            }
            $menuHtml .= '</tbody>
			</table>';
            $response['status'] = true;
            $response['menuHtml'] = $menuHtml;
        } else {
            $response['status'] = false;
        }
        echo json_encode($response);
    }


    public function saveMenu(Request $r)
    {
        $role = $r->role;
        $finalRoleArray = $r->finalRoleArray;
        $filename = storage_path('app/public/rights/' . $role . '.json');
        //$filename = public_path('rights/'.$role.'.json'); 
        if (file_put_contents($filename, $finalRoleArray)) {
            $response['status'] = true;
        } else {
            $response['status'] = false;
        }
        echo json_encode($response);
    }
}
