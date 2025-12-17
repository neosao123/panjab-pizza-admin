<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\Users;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;

class UsersController extends Controller
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
            $data['insertRights'] = $this->rights['insert'];
            $data['users'] = Users::distinct()->select("usermaster.username")
                ->where('usermaster.role', "!=", "R_1")
                ->where('isDelete', 0)
                ->get();
            $data['usersrole'] = UserRole::select("rolesmaster.role")
                ->where('rolesmaster.code', "!=", "R_1")
                ->where('isDelete', 0)
                ->get();
            return view('users.list', $data);
        } else {
            return view('noright');
        }
    }

    public function add()
    {
        if ($this->rights != '' && $this->rights['insert'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $data['usersrole'] = UserRole::select("rolesmaster.role", "rolesmaster.code")
                ->where('rolesmaster.code', "!=", "R_1")
                ->get();
            return view('users.add', $data);
        } else {
            return view('noright');
        }
    }

    public function getUserList(Request $req)
    {
        $username = $req->username;
        //$role = $req->role;
        $search = $req->input('search.value');
        $tableName = "usermaster";
        $orderColumns = array("usermaster.*", "rolesmaster.role as roleName");
        $condition = array('usermaster.isDelete' => array('=', 0), 'usermaster.role' => array('!=', 'R_1'), 'usermaster.username' => array('=', $username));
        $orderBy = array('usermaster' . '.id' => 'DESC');
        $join = array('rolesmaster' => array('rolesmaster.code', 'usermaster.role'));
        $like = array('usermaster.lastname' => $search, 'usermaster.middlename' => $search, 'usermaster.firstname' => $search, 'usermaster.username' => $search, 'usermaster.mobile' => $search, 'usermaster.userEmail' => $search, 'rolesmaster.role' => $search);
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
                    $actions .= '<a class="dropdown-item" href="' . url("users/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';
                }
                if ($this->rights != '' && $this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("users/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights != '' && $this->rights['delete'] == 1) {
                    $actions .= '<a style="cursor:pointer;"class="dropdown-item delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash mr-2" href></i> Delete</a>';
                }

                $actions .= '</div>
						</div>';
                $data[] = array(
                    $srno,
                    $actions,
                    $row->username,
                    $row->firstname . " " . $row->middlename . " " . $row->lastname,
                    $row->mobile,
                    $row->userEmail,
                    $row->roleName,
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

    public function store(Request $r)
    {
        $table = "usermaster";
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];
        $rules = [
            'username' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:100|unique:usermaster,username',
            'firstname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:100',
            'lastname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:100',
            'middlename' => 'nullable|regex:/^[a-zA-Z\s]+$/|min:3|max:100',
            'email' => 'required|unique:usermaster,userEmail',
            'mobilenumber' => 'required|digits:10|unique:usermaster,mobile',
            'profilePhoto' => 'nullable|image|mimes:jpg,png,jpeg',
            'password' => 'required|min:6|max:8|confirmed',
            'password_confirmation' => 'required|min:6|max:8',
            'role' => 'required'
        ];
        $messages = [
            'username.required' => 'User name is required',
            'username.regex' => 'Invalid characters like number, special characters are not allowed',
            'username.min' => 'Minimum of 3 characters are required.',
            'username.max' => 'Max characters exceeded.',
            'firstname.required' => 'First name is required',
            'firstname.regex' => 'Invalid characters like number, special characters are not allowed',
            'firstname.min' => 'Minimum of 3 characters are required.',
            'firstname.max' => 'Max characters exceeded.',
            'lastname.required' => 'Last name is required',
            'lastname.regex' => 'Invalid characters like number, special characters are not allowed',
            'lastname.min' => 'Minimum of 3 characters are required.',
            'lastname.max' => 'Max characters exceeded.',
            'middlename.regex' => 'Invalid characters like number, special characters are not allowed',
            'middlename.min' => 'Minimum of 3 characters are required.',
            'middlename.max' => 'Max characters exceeded.',
            'mobilenumber.required' => 'Mobile Number is required',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be 6 characters long',
            'password.confirmed' => 'Password does not match',
            'password_confirmation.required' => 'Confirm Password is required',
            'password_confirmation.min' => 'Confirm Password must be 6 characters long',
            'password.max' => 'Max characters exceeded.',
            'password_confirmation.max' => 'Max characters exceeded.',
            'email.required' => 'Email is required',
            'username.unique' => 'Username already exist.',
            'mobilenumber.unique' => 'Mobile Number already exist.',
            'email.unique' => 'Email already exist.',

        ];
        $this->validate($r, $rules, $messages);

        $data = [
            'firstname' => ucwords(strtolower($r->firstname)),
            'middlename' => ucwords(strtolower($r->middlename)),
            'lastname' => ucwords(strtolower($r->lastname)),
            'username' => $r->username,
            'role' => $r->role,
            'userEmail' => $r->email,
            'mobile' => $r->mobilenumber,
            'storeLocationCode' => $r->storeLocation,
            'password' => Hash::make($r->password),
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'addIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'addID' => Auth::guard('admin')->user()->code,
        ];
        $currentId = $this->model->addNew($data, $table, 'USR');
        if ($currentId) {
            if ($filenew = $r->file('profilephoto')) {
                $imagename = $currentId . "." . $filenew->getClientOriginalExtension();
                $filenew->move('uploads/profile', $imagename);
                $image_data = ['profilePhoto' => $imagename];
                $image_update = $this->model->doEdit($image_data, $table, $currentId);
            }
            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Users " . $currentId . " is added";
            $this->model->activity_log($data);
            //activity log end

            return redirect('users/list')->with('success', 'Record added successfully');
        }
        return back()->with('error', 'Failed to add the record');
    }

    public function edit(Request $request)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $data['usersrole'] = UserRole::select("rolesmaster.role", "rolesmaster.code")
                ->where('rolesmaster.code', "!=", "R_1")
                ->get();
            $code = $request->code;
            $users = Users::select("usermaster.*", "storelocation.storeLocation")
                ->join("storelocation", "storelocation.code", "=", "usermaster.storeLocationCode", "left")
                ->where('usermaster.code', $code)
                ->first();

            if (!empty($users)) {
                $data['queryresult'] = $users;
                return view('users.edit', $data);
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
            'profilePhoto' => '',
        );
        if (!empty($data)) {
            unlink('uploads/profile/' . $imgNm);
            echo $resultData = $this->model->doEdit($data, 'usermaster', $code);
        } else {
            echo 'false';
        }
    }
    public function update(Request $r)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        if (!empty($r->password) || !empty($r->password_confirmation)) {
            $rules = [
                'username' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:100',
                'firstname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:100',
                'lastname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:100',
                'middlename' => 'nullable|regex:/^[a-zA-Z\s]+$/|min:3|max:100',
                'email' => 'required',
                'mobilenumber' => 'required|digits:10',
                'profilePhoto' => 'nullable|image|mimes:jpg,png,jpeg',
                'password' => 'nullable|min:6|max:8|confirmed',
                'password_confirmation' => 'nullable|min:6|max:8',
                'role' => 'required'
            ];
        } else {
            $rules = [
                'username' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:100',
                'firstname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:100',
                'lastname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:100',
                'middlename' => 'nullable|regex:/^[a-zA-Z\s]+$/|min:3|max:100',
                'email' => 'required',
                'mobilenumber' => 'required|digits:10',
                'profilePhoto' => 'nullable|image|mimes:jpg,png,jpeg',
                'role' => 'required'
            ];
        }

        $messages = [
            'username.required' => 'User name is required',
            'username.regex' => 'Invalid characters like number, special characters are not allowed',
            'username.min' => 'Minimum of 3 characters are required.',
            'username.max' => 'Max characters exceeded.',
            'firstname.required' => 'First name is required',
            'firstname.regex' => 'Invalid characters like number, special characters are not allowed',
            'firstname.min' => 'Minimum of 3 characters are required.',
            'firstname.max' => 'Max characters exceeded.',
            'lastname.required' => 'Last name is required',
            'lastname.regex' => 'Invalid characters like number, special characters are not allowed',
            'lastname.min' => 'Minimum of 3 characters are required.',
            'lastname.max' => 'Max characters exceeded.',
            'middlename.regex' => 'Invalid characters like number, special characters are not allowed',
            'middlename.min' => 'Minimum of 3 characters are required.',
            'middlename.max' => 'Max characters exceeded.',
            'mobilenumber.required' => 'Mobile Number is required',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be 6 characters long',
            'password.confirmed' => 'Password does not match',
            'password_confirmation.required' => 'Confirm Password is required',
            'password_confirmation.min' => 'Confirm Password must be 6 characters long',
            'email.required' => 'Email is required',
            'password.max' => 'Max characters exceeded.',
            'password_confirmation.max' => 'Max characters exceeded.',
            'username.unique' => 'Username already exists.',
            'mobilenumber.unique' => 'Mobile Number already exists.',
            'email.unique' => 'Email already exists.',

        ];

        $this->validate($r, $rules, $messages);

        $code = $r->code;
        $table = 'usermaster';
        $where[] = ["$table.userEmail", "=", $r->email];
        $where[] = ["$table.code", "!=", $code];
        $duplicate = $this->model->checkForDuplicate($table, $table, $where);
        if ($duplicate) {
            return back()->with('error', 'Duplicate email address exists.');
        }

        $where1[] = ["$table.username", "=", $r->username];
        $where1[] = ["$table.code", "!=", $code];
        $duplicate1 = $this->model->checkForDuplicate($table, $table, $where1);
        if ($duplicate1) {
            return back()->with('error', 'Duplicate username exists.');
        }

        $where2[] = ["$table.mobile", "=", $r->mobilenumber];
        $where2[] = ["$table.code", "!=", $code];
        $duplicate2 = $this->model->checkForDuplicate($table, $table, $where2);
        if ($duplicate2) {
            return back()->with('error', 'Duplicate mobile number exist');
        }

        if (!empty($r->input('password')) || !empty($r->input('password_confirmation'))) {
            $data = [
                'firstname' => ucwords(strtolower($r->firstname)),
                'middlename' => ucwords(strtolower($r->middlename)),
                'lastname' => ucwords(strtolower($r->lastname)),
                'username' => $r->username,
                'role' => $r->role,
                'userEmail' => $r->email,
                'mobile' => $r->mobilenumber,
                'storeLocationCode' => $r->storeLocation,
                'password' => Hash::make($r->password),
                'isActive' => $r->isActive == "" ? '0' : 1,
                'isDelete' => 0,
                'editIP' => $ip,
                'editDate' => $currentdate->toDateTimeString(),
                'editID' => Auth::guard('admin')->user()->code
            ];
        } else {
            $data = [
                'firstname' => ucwords(strtolower($r->firstname)),
                'middlename' => ucwords(strtolower($r->middlename)),
                'lastname' => ucwords(strtolower($r->lastname)),
                'username' => $r->username,
                'role' => $r->role,
                'userEmail' => $r->email,
                'mobile' => $r->mobilenumber,
                'storeLocationCode' => $r->storeLocation,
                'isActive' => $r->isActive == "" ? '0' : 1,
                'isDelete' => 0,
                'editIP' => $ip,
                'editDate' => $currentdate->toDateTimeString(),
                'editID' => Auth::guard('admin')->user()->code
            ];
        }

        $result = $this->model->doEdit($data, $table, $code);
        if ($filenew = $r->file('profilephoto')) {
            $imagename = $code . "." . $filenew->getClientOriginalExtension();
            $filenew->move('uploads/profile', $imagename);
            $image_data = ['profilePhoto' => $imagename];
            $image_update = $this->model->doEdit($image_data, $table, $code);
        }
        if ($result == true || $image_update == true) {
            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	User " . $code . " is updated.";
            $this->model->activity_log($data);
            //activity log end
            return redirect('users/list')->with('success', 'User updated successfully');
        } else {
            return back()->with('error', 'Failed to update the user');
        }
    }

    public function delete(Request $r)
    {

        $currentdate = Carbon::now();
        $code = $r->code;
        $ip = $_SERVER['REMOTE_ADDR'];
        $today = date('Y-m-d H:i:s');
        $table = 'usermaster';
        $data = ['isActive' => 0, 'isDelete' => 1, 'deleteIP' => $ip, 'deleteID' => Auth::guard('admin')->user()->code, 'deleteDate' => $today];

        //activity log start
        $datastring = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	User " . $code  . " is deleted.";
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
            DB::enableQueryLog();
            $code = $request->code;
            $users = Users::select("usermaster.*", "rolesmaster.role as roleName", "storelocation.storeLocation")
                ->join("storelocation", "storelocation.code", "=", "usermaster.storeLocationCode", "left")
                ->join("rolesmaster", "rolesmaster.code", "=", "usermaster.role")
                ->where('usermaster.code', $code)
                ->first();

            $query_1 = DB::getQueryLog();
            if (!empty($users)) {
                $queryresult = $users;
                return view('users.view', compact('queryresult'));
            }
        } else {
            return view('noright');
        }
    }
}
