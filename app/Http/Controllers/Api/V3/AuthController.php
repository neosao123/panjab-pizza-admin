<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
// Models
use App\Models\Customer;
use App\Models\Customeraddress;
use App\Models\GlobalModel;
use App\Models\ApiModel;


class AuthController extends Controller
{
  public function __construct(GlobalModel $model, ApiModel $apimodel)
  {
    $this->model = $model;
    $this->apimodel = $apimodel;
  }
  // POST - User Login
  public function user_login(Request $r)
  {
    try {
      $input = $r->all();
      $validator = Validator::make($input, [
        'username' => 'required|digits:10',
        'password' => 'required'
      ]);
      if ($validator->fails()) {
        $response = [
          'status' => 500,
          "message" => $validator->errors()->first()
        ];
        return response()->json($response, 200);
      }
      $customer = Customer::where("customer.mobileNumber", $r->username)
        ->where("isActive", 1)
        ->where("isDelete", 0)
        ->first();
      if (!$customer ||  !Hash::check($r->password, $customer->password)) {
        return response()->json(['status' => 300,  "message" => "Invalid phone number or password"], 200);
      } else {
        $token = $customer->createToken('MrsinghPizzaCustomer')->plainTextToken;

        $data['customerCode'] = $customer->code;
        $data['firstName'] = $customer->firstName ?? "";
        $data['lastName'] = $customer->lastName ?? "";
        $data['fullName'] = $customer->fullName ?? "";
        $data['mobileNumber'] = $customer->mobileNumber ?? "";
        $data['isActive'] = $customer->isActive;
        $data['email'] = $customer->email ?? "";
        $path = "";
        if ($customer->profilePhoto != "" && $customer->profilePhoto != null) {
          $path = url("uploads/customer/" . $customer->profilePhoto);
        }
        $data['profilePhoto'] = $path;

        // Developer: Shreyas Mahamuni, Wokring Date: 23-12-2023
        $customerAddress = Customeraddress::where('customerCode', $customer->code)->first();
        $data['address'] = $customerAddress->address;
        $data['city'] = $customerAddress->city;
        $data['zipcode'] = $customerAddress->zipcode;

        return response()->json(['status' => 200, "message" => "Data found", "data" => $data, "token" => $token], 200);
      }
    } catch (\Exception $ex) {
      return response()->json(['status' => 400, 'message' => $ex->getMessage()], 400);
    }
  }
  // POST - User Registration
  public function user_registration(Request $r)
  {
    try {
      $currentdate = Carbon::now();
      $input = $r->all();
      $rules = [
        'firstName' => 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/',
        'lastName' => 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/',
        'email' => [
          'required',
          'email',
          Rule::unique('customer')->where(function ($query) {
            return $query->where('isDelete', '=', '0');
          })
        ],
        'mobileNumber' => [
          'required',
          'digits:10',
          'numeric',
          Rule::unique('customer')->where(function ($query) {
            return $query->where('isDelete', '=', '0');
          })
        ],
        'address' => 'required|min:5',
        'city'  => 'required|min:2',
        'zipcode' => 'required|regex:/^[A-Za-z]\d[A-Za-z]\d[A-Za-z]\d$/',
        'password' => 'required|min:6|max:20',
        'password_confirmation' => 'min:6|max:20|required|same:password',
        'profilePhoto' => 'nullable|mimes:png,jpg,jpeg,gif',
      ];

      $messages = [
        'firstName.required' => 'First name is required',
        'firstName.regex' => 'Invalid characters like number, special characters are not allowed',
        'firstName.min' => 'Minimum of 3 characters are required.',
        'firstName.max' => 'Max characters exceeded.',
        'lastName.required' => 'Last name is required',
        'lastName.regex' => 'Invalid characters like number, special characters are not allowed',
        'lastName.min' => 'Minimum of 3 characters are required.',
        'lastName.max' => 'Max characters exceeded.',
        'mobileNumber.required' => 'Mobile number is required.',
        'mobileNumber.unique' => 'Mobile number is already exist.',
        'password.required' => 'Password is required',
        'password_confirmation.required' => 'Confirm Password is required',
        'password.min' => 'Password must be 6 characters long',
        'password.max' => 'Max characters exceeded.',
        'password.confirmed' => 'Password does not match',
        'zipcode.required' => 'Postal Code is required',
        'zipcode.regex' => 'Enter valid postal code.',
        'address.required' => 'Address is required.',
        'address.min' => 'Minimum of 5 characters are required.',
        'city.required' => 'City is required.',
        'city.min' => 'Minimum of 2 characters are required.',
      ];
      $validator = Validator::make($input, $rules, $messages);
      if ($validator->fails()) {
        $response = [
          'status' => 500,
          "message" => $validator->errors()->first()
        ];
        return response()->json($response, 200);
      }

      $insertData = [
        "firstName"     =>  $r->firstName,
        "lastName"      =>  $r->lastName,
        "fullName"      =>  ucwords(strtolower($r->firstName . " " . $r->lastName)),
        "mobileNumber"  =>  $r->mobileNumber,
        "email"         =>  $r->email,
        "isActive"      =>  1,
        "isDelete"      =>  0,
        "addDate"       =>  $currentdate->toDateTimeString(),
        'password'      =>  Hash::make($r->password)
      ];
      $currentId = $this->model->addNew($insertData, "customer", 'CST');
      if ($currentId) {
        $insertData = [
          "customerCode"  => $currentId,
          "address"       => $r->address,
          "city"          => $r->city,
          "zipcode"       => $r->zipcode,
          "isActive"      => 1,
          "isDelete"      => 0,
          "addDate"       => date("Y-m-d H:i:s")
        ];

        $this->model->addNew($insertData, "customeraddress", 'ADR');

        $customer = Customer::where("customer.mobileNumber", $r->mobileNumber)->first();
        $imagename = "";
        if ($r->hasFile('profilePhoto')) {
          $filenew = $r->file('profilePhoto');
          $imagename = $customer->code . "_" . time() . "." . $filenew->getClientOriginalExtension();
          $filenew->move('uploads/customer', $imagename);
          $image_data = ['profilePhoto' => $imagename];
          $imageUpdate = Customer::where("code", $customer->code)->update(["profilePhoto" => $imagename]);
        }
        $path = "";
        if ($imagename != "" && $imagename != null) {
          $path = url("uploads/customer/" . $imagename);
        }
        $data['customerCode'] = $customer->code;
        $data['firstName'] = $customer->firstName ?? "";
        $data['lastName'] = $customer->lastName ?? "";
        $data['fullName'] = $customer->fullName ?? "";
        $data['mobileNumber'] = $customer->mobileNumber ?? "";
        $data['isActive'] = $customer->isActive;
        $data['email'] = $customer->email ?? "";
        $data['profilePhoto'] = $path;

        // Developer: Shreyas Mahamuni, Working Date: 23-12-2023
        $customerAddress = Customeraddress::where('customerCode', $customer->code)->first();
        $data['address'] = $customerAddress->address;
        $data['city'] = $customerAddress->city;
        $data['zipcode'] = $customerAddress->zipcode;

        $token = $customer->createToken('MrsinghPizzaCustomer')->plainTextToken;

        return response()->json(['status' => 200, 'message' => 'Account registered successfully', 'data' => $data, 'token' => $token], 200);
      }
      return response()->json(['status' => 300, 'message' => 'Failed to register account.'], 200);
    } catch (\Exception $ex) {
      return response()->json(['status' => 400, 'message' => $ex->getMessage()], 400);
    }
  }
  // POST - User Logout
  public function user_logout(Request $r)
  {
    try {
      $input = $r->all();
      $validator = Validator::make($input, [
        'customerCode' => 'required'
      ]);
      if ($validator->fails()) {
        $response = [
          'status' => 500,
          "message" => $validator->errors()->first()
        ];
        return response()->json($response, 200);
      }
      $customer = Customer::select('customer.*')
        ->where("customer.code", $r->customerCode)
        ->where("isActive", 1)
        ->where("isDelete", 0)
        ->first();

      if (!empty($customer)) {
        $customer->tokens()
          ->where('tokenable_id', $customer->id)
          ->where('name', 'MrsinghPizzaCustomer')
          ->delete();
        return response()->json(['status' => 200, "message" => "Logout successfully"], 200);
      } else {
        return response()->json(['status' => 300, "message" => "Customer not found"], 200);
      }
    } catch (\Exception $ex) {
      return response()->json(['status' => 400, 'message' => $ex->getMessage()], 400);
    }
  }
  // POST - User Changes Password
  public function user_reset_password(Request $r)
  {
    try {
      $validator = Validator::make($r->all(), [
        'mobileNumber' => 'required|digits:10',
        'password' => 'min:6|max:20|confirmed|required',
        'password_confirmation' => 'min:6|max:20|required|same:password',
      ]);
      if ($validator->fails()) {
        $response = [
          'status' => 500,
          "message" => $validator->errors()->first()
        ];
        return response()->json($response, 200);
      }

      $customer = Customer::where('mobileNumber', $r->mobileNumber)->first();
      if ($customer) {
        $customer->password = Hash::make($r->password);
        $customer->save();
        return response()->json(['status' => 200, "message" => "Password updated successful. Please login to continue."], 200);
      }
      return response()->json(['status' => 300, "message" => "Customer not found"], 200);
    } catch (\Exception $ex) {
      return response()->json(['status' => 400, 'message' => $ex->getMessage()], 400);
    }
  }
}
