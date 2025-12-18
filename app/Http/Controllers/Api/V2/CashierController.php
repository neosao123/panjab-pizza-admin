<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\OrderMaster;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\ApiModel;
use App\Models\Users;
use App\Models\PersonalAccessToken;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Config;
use App\Classes\FirebaseNotification;

class CashierController extends Controller
{
    public function __construct(GlobalModel $model, ApiModel $apimodel)
    {
        $this->model = $model;
        $this->apimodel = $apimodel;
    }

    public function send_trial_notification(Request $r)
    {
        $firebase_tokens = [$r->fb_token];
        $title = $r->title ?? "Trial Notification " . date("Y-m-d h:i a");
        $message = "This is an sample notification send from the api of mr.singhs pizza application";
        $random = rand(1, 9999);
        $dataArr = $notification = array();
        $dataArr['device_id'] = $firebase_tokens;
        $dataArr['message'] = $message;
        $dataArr['title'] = $title;
        $dataArr['random_id'] = $random;
        $notification['device_id'] = $firebase_tokens;
        $notification['message'] = $message;
        $notification['title'] = $title;
        $notification['random_id'] = $random;
        $fbNotification = new FirebaseNotification();
        $res = $fbNotification->sendNotification($dataArr, $notification);
        return response()->json(["res" => $res, "fbIds" => $firebase_tokens], 200);
    }

    public function cashier_login(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'userName' => 'required',
                'password' => 'required'
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $cashier = Users::where("usermaster.username", $r->userName)
                ->whereIn("role", ["R_3", "R_4"])
                ->where("isActive", 1)
                ->where("isDelete", 0)
                ->first();
            if (!$cashier ||  !Hash::check($r->password, $cashier->password)) {
                return response()->json(["message" => "Invalid username or password"], 200);
            } else {
                $token = $cashier->createToken('MrsinghPizzaCashier')->plainTextToken;
                $getUserDetails = $this->get_cashier_details("", "", $r->userName, "");
                if ($getUserDetails != false) {
                    $userDeatils = $getUserDetails;
                    return response()->json(["message" => "Data found", "data" => $userDeatils, "token" => $token], 200);
                } else {
                    return response()->json(["message" => "Data not found"], 200);
                }
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function cashier_reset_password(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'email' => 'required|email'
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $user = Users::where('userEmail', $r->email)->first();

            if ($user) {
                $token = $this->model->randomCharacters(5);
                $token .= date('Hdm');
                $resetLink = env('CASHIER_URL') . '/cashier/verifyToken/' . $token;

                $details = [
                    'title' => 'Mail from Mr singh Pizza',
                    'link' => $resetLink,
                ];
                \Mail::to($r->email)->send(new \App\Mail\ForgotAdminEmail($details));
                $user->resetToken = $token;
                $user->save();
                return response()->json(['message' => 'Reset Link was sent to your email...'], 200);
            } else {
                return response()->json(['message' => 'No users were found with the email address provided! Sorry cannot reset the password'], 400);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function verify_cashier_token(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'token' => 'required'
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $user = Users::where('resetToken', $r->token)->first();
            if ($user) {
                $data = ["resetToken" => $user->resetToken];
                return response()->json(["message" => "Data found", "data" => $data], 200);
            }
            return response()->json(["message" => "Password Reset Link is Expired. Please Forgot Password Again to Continue."], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function update_cashier_password(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'token' => 'required',
                'password' => 'min:6|max:8|confirmed|required',
                'password_confirmation' => 'min:6|max:8|required',
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $user = Users::where('resetToken', $r->token)->first();
            if ($user) {
                $user->password = Hash::make($r->password);
                $user->resetToken = null;
                $user->save();
                return response()->json(["message" => "Password updated successful. Please login to continue."], 200);
            }
            return response()->json(["message" => "Password Reset Link is Expired. Please Forgot Password Again to Continue."], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function get_cashier_info(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'cashierCode' => 'required'
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $getCashierDetails = $this->get_cashier_details($r->cashierCode, "", "", 1);
            if ($getCashierDetails != false) {
                return response()->json(["message" => "Data found", "data" => $getCashierDetails], 200);
            } else {
                return response()->json(["message" => "Data not found"], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function update_cashier_info(Request $r)
    {
        try {
            $input = $r->all();
            $cashierCode = $r->cashierCode;

            $messages = [
                'cashierCode.required' => 'Cashier code is required.',

                'firstName.required' => 'First name is required.',
                'firstName.min' => 'First name must be at least 3 characters.',
                'firstName.max' => 'First name must not exceed 100 characters.',
                'firstName.regex' => 'First name can contain only letters and spaces.',

                'lastName.required' => 'Last name is required.',
                'lastName.min' => 'Last name must be at least 3 characters.',
                'lastName.max' => 'Last name must not exceed 100 characters.',
                'lastName.regex' => 'Last name can contain only letters and spaces.',

                'userEmail.required' => 'Email address is required.',
                'userEmail.email' => 'Please enter a valid email address.',
                'userEmail.unique' => 'Email address already exists.',

                'mobile.required' => 'Mobile number is required.',
                'mobile.regex' => 'Enter a valid Canadian mobile number.',
                'mobile.unique' => 'Mobile number already exists.',

                'username.required' => 'Username is required.',
                'username.min' => 'Username must be at least 3 characters.',
                'username.max' => 'Username must not exceed 30 characters.',
                'username.regex' => 'Username can contain only letters and spaces.',
                'username.unique' => 'Username already exists.',

                'profilePhoto.mimes' => 'Profile photo must be png, jpg, jpeg, or gif.',
            ];

            $validator = Validator::make($input, [
                'cashierCode' => 'required',

                'firstName' => 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/',
                'lastName' => 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/',

                'userEmail' => [
                    'required',
                    'email',
                    Rule::unique('usermaster')
                        ->where(fn($q) => $q->where('isDelete', 0))
                        ->ignore($cashierCode, 'code')
                ],

                'mobile' => [
                    'required',
                    'regex:/^[2-9]\d{9}$/',
                    Rule::unique('usermaster')
                        ->where(fn($q) => $q->where('isDelete', 0))
                        ->ignore($cashierCode, 'code')
                ],

                'username' => [
                    'required',
                    'min:3',
                    'max:30',
                    'regex:/^[a-zA-Z\s]+$/',
                    Rule::unique('usermaster')
                        ->where(fn($q) => $q->where('isDelete', 0))
                        ->ignore($cashierCode, 'code')
                ],

                'profilePhoto' => 'nullable|mimes:png,jpg,jpeg,gif'
            ], $messages);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $cashier = Users::where('code', $cashierCode)->first();

            if (!$cashier) {
                return response()->json(['message' => 'Invalid user. Please login again.'], 401);
            }

            if ($r->hasFile('profilePhoto')) {
                $file = $r->file('profilePhoto');
                $imagename = $cashierCode . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/profile', $imagename);
                $cashier->profilePhoto = $imagename;
            }

            $cashier->username = $r->username;
            $cashier->firstname = $r->firstName;
            $cashier->lastname = $r->lastName;
            $cashier->userEmail = $r->userEmail;
            $cashier->mobile = $r->mobile;
            $cashier->save();

            $data = $this->get_cashier_details($cashierCode, "", "", 1);

            return response()->json([
                'message' => 'Profile details updated successfully',
                'data' => $data
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }


    public function get_cashier_details(String $userCode, String $mobileNumber, String $userName, String $type)
    {
        $query = DB::table("usermaster");
        if ($type == "1") {
            $query->where("usermaster.code", $userCode);
        } else if ($type == "2") {
            $query->where("usermaster.mobileNumber", $mobileNumber);
        } else {
            $query->where("usermaster.username", $userName);
        }
        $query->where("isActive", 1);
        $result = $query->first();
        if (!empty($result)) {
            $path = "";
            if ($result->profilePhoto != "" && $result->profilePhoto != null) {
                $path = url("uploads/profile/" . $result->profilePhoto);
            }
            $data['code'] = $result->code;
            $data['userName'] = $result->username ?? "";
            $data['firstName'] = $result->firstname ?? "";
            $data['middleName'] = $result->middlename ?? "";
            $data['lastName'] = $result->lastname ?? "";
            $data['mobileNumber'] = $result->mobile ?? "";
            $data['email'] = $result->userEmail ?? "";
            $data['isActive'] = $result->isActive;
            $data['firebaseId'] = $result->firebase_id ?? "";
            $data['profilePhoto'] = $path;
            $data['storeLocation'] = $result->storeLocationCode ?? "";
            $data['role'] = $result->role ?? "";
            return $data;
        }
        return false;
    }

    public function cashier_logout(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'cashierCode' => 'required'
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $users = Users::select('usermaster.*')
                ->where("usermaster.code", $r->cashierCode)
                ->where("isActive", 1)
                ->where("isDelete", 0)
                ->where("role", "R_3")
                ->first();

            if (!empty($users)) {
                //$users->tokens()->delete();
                $users->tokens()
                    ->where('tokenable_id', $users->id)
                    ->where('name', 'MrsinghPizzaCashier')
                    ->delete();
                return response()->json(["message" => "Logout successfully"], 200);
            } else {
                return response()->json(["message" => "Cashier not found"], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function cashier_details_by_token(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'token' => 'required'
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            [$id, $cashier_token] = explode('|', $r->token, 2);
            $getCashierDetails = PersonalAccessToken::where('token', hash('sha256', $cashier_token))->first();
            if (!empty($getCashierDetails)) {
                $getUserDetails = Users::select("usermaster.*")
                    ->where("usermaster.id", $getCashierDetails->tokenable_id)
                    ->first();
                if (!empty($getUserDetails)) {
                    $path = "";
                    if ($getUserDetails->profilePhoto != "" && $getUserDetails->profilePhoto != null) {
                        $path = url("uploads/profile/" . $getUserDetails->profilePhoto);
                    }
                    $data['code'] = $getUserDetails->code;
                    $data['userName'] = $getUserDetails->username ?? "";
                    $data['firstName'] = $getUserDetails->firstname ?? "";
                    $data['middleName'] = $getUserDetails->middlename ?? "";
                    $data['lastName'] = $getUserDetails->lastname ?? "";
                    $data['mobileNumber'] = $getUserDetails->mobile ?? "";
                    $data['email'] = $getUserDetails->userEmail ?? "";
                    $data['isActive'] = $getUserDetails->isActive;
                    $data['firebaseId'] = $getUserDetails->firebase_id ?? "";
                    $data['profilePhoto'] = $path;
                    return response()->json(["message" => "Cashier found", "data" => $data], 200);
                }
                return response()->json(["message" => "Cashier not found"], 200);
            }
            return response()->json(["message" => "Invalid Token"], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function change_password(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'cashierCode' => 'required',
                'password' => 'min:6|max:20|confirmed|required',
                'password_confirmation' => 'min:6|max:20|required',
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $user = Users::where('code', $r->cashierCode)->first();
            if ($user) {
                $user->password = Hash::make($r->password);
                $user->save();
                return response()->json(["message" => "Password updated successfully."], 200);
            }
            return response()->json(["message" => "Failed to update the password. Please try again"], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function update_firebase_token(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'cashierCode' => 'required',
                'firebaseId'  => 'required',
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $user = DB::table('usermaster')->where("code", $r->cashierCode)->first();
            if (!empty($user)) {
                DB::table('usermaster')->where("code", $r->cashierCode)->update([
                    "firebase_id" => $r->firebaseId,
                ]);
                return response()->json(["message" => "Firebase Id Updated Succefully."], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    // Developer: Shreyas Mahamuni, Wokring Time: 02-12-2023, 8-12-2023
    // This Function return prev order address for cashier when onchange mobile number
    // Added Some Functionality in this function - Get Postal Code from last order, Get Credit Comment from previous last order
    public function getPrevAddress(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'mobileNumber' => 'required',
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $prevOrder = OrderMaster::where('mobileNumber', $r->mobileNumber)->where('deliveryType', "delivery")->orderBy('id', 'desc')->first();
            if (!empty($prevOrder)) {
                $data['prevOrderAddress'] = $prevOrder->address;
                $data['customerName'] = $prevOrder->customerName;
                $data['postalCode'] = $prevOrder->zipCode;

                return response()->json(["message" => "Success.", "data" => $data], 200);
            } else {
                return response()->json(["message" => "Previous orders are not found.",], 400);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }
}
