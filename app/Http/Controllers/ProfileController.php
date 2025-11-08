<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }
    public function index(Request $r, $id = '')
    {
        $code = $r->id;
        $table = 'usermaster';
        $condition = array('usermaster.code' => $code);
        $details = $this->model->read_user_information($table, $condition);
        if ($details != false) {
            return view('profile.edit', compact('details'));
        }
    }
    public function show(Request $request, $id = '')
    {
        $code = $request->id;
        $table = 'usermaster';
        $condition = array('usermaster.code' => $code);
        $details = $this->model->read_user_information($table, $condition);
        if ($details != false) {
            return view('profile.viewprofile', compact('details'));
        }
    }
    public function updateprofile(Request $request, $id = '')
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        if (!empty($request->input('password')) || !empty($request->input('password_confirmation'))) {
            $rules = [
                'userEmail' => 'required',
                'password'  => 'min:6|confirmed',
                'name' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
                'mobilenumber' => 'required|min:10|max:12',
                'firstname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
                'lastname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
                'middlename' => 'nullable|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
            ];
        } else {
            $rules = [
                'userEmail' => 'required',
                'firstname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
                'lastname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
                'middlename' => 'nullable|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
                'mobilenumber' => 'required|min:10|max:12',
                'name' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
            ];
        }
        $messages = [
            'userEmail.required' => 'Email is required',
            'password.confirmed' => 'Password does not match',
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
            'mobilenumber.min' => 'Invalid mobile number (Min 10 digits)',
            'mobilenumber.max' => 'Invalid mobile number (Max 12 digits)',
            'name.required' => 'User name is required',
            'name.regex' => 'Invalid characters like number, special characters are not allowed',
            'name.min' => 'Minimum of 3 characters are required.',
            'name.max' => 'Max characters exceeded.',

        ];
        $this->validate($request, $rules, $messages);
        $code = $request->input('code');
        $table = 'usermaster';
        $where[] = ["$table.userEmail", "=", $request->userEmail];
        $where[] = ["$table.code", "!=", $code];
        $duplicate = $this->model->checkForDuplicate($table, $table, $where);
        if ($duplicate) {
            return back()->with('error', 'Duplicate email address exits');
        }

        $where1[] = ["$table.username", "=", $request->username];
        $where1[] = ["$table.code", "!=", $code];
        $duplicate1 = $this->model->checkForDuplicate($table, $table, $where1);
        if ($duplicate1) {
            return back()->with('error', 'Duplicate username exits');
        }

        $where2[] = ["$table.mobile", "=", $request->mobilenumber];
        $where2[] = ["$table.code", "!=", $code];
        $duplicate2 = $this->model->checkForDuplicate($table, $table, $where2);
        if ($duplicate2) {
            return back()->with('error', 'Duplicate mobile number exits');
        }


        if (!empty($request->input('password')) || !empty($request->input('password_confirmation'))) {
            $data = [
                'username' => $request->input('name'),
                'userEmail' => $request->input('userEmail'),
                'firstname' => $request->firstname,
                'middlename' => $request->middlename,
                'lastname' => $request->lastname,
                'password' => Hash::make($request->input('password')),
                'mobile' => $request->mobilenumber,
                'editIP' => $ip,
                'editDate' => $currentdate->toDateTimeString(),
                'editID' => $code
            ];
        } else {
            $data = [
                'username' => $request->input('name'),
                'userEmail' => $request->input('userEmail'),
                'firstname' => $request->firstname,
                'middlename' => $request->middlename,
                'lastname' => $request->lastname,
                'mobile' => $request->mobilenumber,
                'editIP' => $ip,
                'editDate' => $currentdate->toDateTimeString(),
                'editID' => $code
            ];
        }

        $result = $this->model->doEdit($data, $table, $code);

        if ($filenew = $request->file('profilephoto')) {
            $imagename = $filenew->getClientOriginalName();
            $filenew->move('public/uploads/profile', $imagename);
            $image_data = ['profilePhoto' => $imagename];
            $image_update = $this->model->doEdit($image_data, $table, $code);
        }

        if ($result == true || $image_update == true) {

            //activity log start
            $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . Auth::guard('admin')->user()->code .    "	Profile " . $code . " is updated.";
            $this->model->activity_log($data);
            //activity log end

            return redirect()->back()->with('status', ['message' => "Profile Updated Successfully"]);
        } else {
            return redirect()->back()->with('status', ['message' => "Something went to wrong"]);
        }
    }
}
