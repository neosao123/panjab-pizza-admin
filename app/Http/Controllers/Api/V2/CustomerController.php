<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\DynamicSliderLineentries;
use App\Models\Storelocation;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\ApiModel;
use App\Models\Customer;
use App\Models\Customeraddress;
use App\Models\DynamicSlider;
use App\Models\PersonalAccessToken;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;
use App\Models\CustomerOtp;
use App\Classes\Twilio;
use Illuminate\Support\Facades\Config;
use App\Models\SmsTemplate;

class CustomerController extends Controller
{
    public function __construct(GlobalModel $model, ApiModel $apimodel)
    {
        $this->model = $model;
        $this->apimodel = $apimodel;
    }

    public function randomCharacters($n)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    public function randomOTP($n)
    {
        $characters = '0123456789';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    public function customer_login(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'username' => 'required|digits:10',
                'password' => 'required'
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $customer = Customer::where("customer.mobileNumber", $r->username)
                ->where("isActive", 1)
                ->where("isDelete", 0)
                ->first();
            if (!$customer ||  !Hash::check($r->password, $customer->password)) {
                return response()->json(["message" => "Invalid phone number or password"], 400);
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

                return response()->json(["message" => "Data found", "data" => $data, "token" => $token], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function customer_register(Request $r)
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
                'zipcode' => 'required|regex:/^[ABCEGHJKLMNPRSTVXY]\d[A-Z]\d[A-Z]\d$/i',
                'password' => 'required|min:6|max:20',
                'password_confirmation' => 'min:6|max:20|required',
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
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
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

                return response()->json(['message' => 'Account registered successfully', 'data' => $data, 'token' => $token], 200);
            }
            return response()->json(['message' => 'Failed to register account.'], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function update_customer_info(Request $r)
    {
        try {
            $currentdate = Carbon::now();
            $input = $r->all();
            $customerCode = $r->customerCode;
            $validator = Validator::make($input, [
                'customerCode' => 'required',
                'firstName' => 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/',
                'lastName' => 'required|min:3|max:100|regex:/^[a-zA-Z\s]+$/',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('customer')->where(function ($query) use ($customerCode) {
                        return $query->where('code', "!=", $customerCode)->where('isDelete', '=', '0');
                    })
                ],
                'mobileNumber' => [
                    'required',
                    'digits:10',
                    'numeric',
                    Rule::unique('customer')->where(function ($query) use ($customerCode) {
                        return $query->where('code', "!=", $customerCode)
                            ->where('isDelete', '=', '0');
                    })
                ],
                'profilePhoto' => 'nullable|mimes:png,jpg,jpeg,gif',
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $customer = Customer::where('code', $customerCode)->first();
            if (!empty($customer)) {
                $imagename = "";
                if ($r->hasFile('profilePhoto')) {
                    $filenew = $r->file('profilePhoto');
                    $imagename = $customerCode . "_" . time() . "." . $filenew->getClientOriginalExtension();
                    $filenew->move('uploads/customer', $imagename);
                    $image_data = ['profilePhoto' => $imagename];
                    Customer::where("code", $customerCode)->update(["profilePhoto" => $imagename]);
                }
                $customer->firstName = $r->firstName;
                $customer->lastName = $r->lastName;
                $customer->email = $r->email;
                $customer->fullName = ucwords(strtolower($r->firstName . " " . $r->lastName));
                $customer->mobileNumber = $r->mobileNumber;
                $customer->save();
                sleep(0.5);

                $getCustomerDetails = $this->get_customer_details($r->customerCode, "", "", 1);
                if ($getCustomerDetails != false) {
                    return response()->json(["message" => "Data found", "data" => $getCustomerDetails], 200);
                } else {
                    return response()->json(["message" => "Data not found"], 400);
                }
            }
            return response()->json(['message' => "Invalid customer"], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function add_customer_address(Request $r)
    {
        try {
            $currentdate = Carbon::now();
            $input = $r->all();
            $customerCode = $r->customerCode;
            $validator = Validator::make($input, [
                'customerCode' => 'required',
                'address' => 'required|min:2|max:100',
                'city' => 'required|min:2|max:100',
                'zipcode' => 'required|regex:/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/',
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $customer = Customer::where('code', $customerCode)->first();
            if (!empty($customer)) {
                $data = [
                    "customerCode"  => $r->customerCode,
                    "address"       => $r->address,
                    "city"          => $r->city,
                    "zipcode"       => $r->zipcode,
                    "isActive"      => 1,
                    "isDelete"      => 0,
                    "addDate"       => $currentdate->toDateTimeString(),
                ];
                $currentId = $this->model->addNew($data, "customeraddress", 'CSTA');
                if ($currentId) {
                    return response()->json(['message' => "Customer address added successfully."], 200);
                }
                return response()->json(['message' => "Failed to add customer address."], 400);
            }
            return response()->json(['message' => "Invalid customer"], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function update_customer_address(Request $r)
    {
        try {
            $currentdate = Carbon::now();
            $input = $r->all();
            $customerCode = $r->customerCode;
            $validator = Validator::make($input, [
                'addressCode' => 'required',
                'customerCode' => 'required',
                'street' => 'required|min:2|max:100',
                'city' => 'required|min:2|max:100',
                'landmark' => 'required|min:2|max:100',
                'zipcode' => 'required|regex:/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/',
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $customer = Customer::where('code', $customerCode)->first();
            if (!empty($customer)) {
                $customerAddress = Customeraddress::where('code', $r->addressCode)->first();
                $customerAddress->customerCode = $r->customerCode;
                $customerAddress->street = $r->street;
                $customerAddress->city = $r->city;
                $customerAddress->landmark = $r->landmark;
                $customerAddress->zipcode = $r->zipcode;
                $customerAddress->addDate = $currentdate->toDateTimeString();
                $customerAddress->save();
                sleep(1);
                return response()->json(['message' => "Customer address updated successfully"], 200);
            }
            return response()->json(['message' => "Invalid customer"], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function delete_customer_address(Request $r)
    {
        try {
            $currentdate = Carbon::now();
            $input = $r->all();
            $customerCode = $r->customerCode;
            $validator = Validator::make($input, [
                'addressCode' => 'required',
                'customerCode' => 'required',
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $customer = Customer::where('code', $customerCode)->first();
            if (!empty($customer)) {
                $customerAddress = Customeraddress::where('code', $r->addressCode)->first();
                $customerAddress->isActive = '0';
                $customerAddress->isDelete = '1';
                $customerAddress->save();
                sleep(1);
                return response()->json(['message' => "Customer address deleted successfully"], 200);
            }
            return response()->json(['message' => "Invalid customer"], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }


    //customer reset password

    public function customer_reset_password(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'mobileNumber' => 'required'
            ]);
            if ($validator->fails()) {
                $response = [
                    "status"=>"300",
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }
            $customer = Customer::where('mobileNumber', $r->mobileNumber)
                       ->where("isActive",1)
                       ->where("isDelete",0)
                       ->first();
            if ($customer) {
                $token = $this->model->randomCharacters(5);
                $customer->resetToken=$token;
                $customer->save();

                CustomerOtp::where('mobile', $r->mobileNumber)->delete();

                $otp = $this->generateOTP($r->mobileNumber);

                if ($otp === false) {
                    return response()->json(["status"=>"300","message" => "Failed to generate OTP. Please try again."], 200);
                }

                $smsTemplate = SmsTemplate::where("id", 6)->first();

                // Replace placeholders in template with actual values
                $message = str_replace(
                    ['{otp}'],
                    [$otp],
                    $smsTemplate->template
                );
                $twilio = new Twilio;

                if ($twilio->isLive()) {
                    $sms = $twilio->sendMessage($message, $r->mobileNumber);

                    if ($sms === false) {
                        return response()->json([
                            "status"=>"300",
                            "message" => "Failed to send OTP. Please try again later."
                        ], 200);
                    }

                    return response()->json([
                        "status"=>"200",
                        "message" => "OTP sent successfully",
                        "mobileNumber" => $r->mobileNumber,
                        "resetToken"=>$token
                    ], 200);
                }

                if (env('SMS_MODE') === "TEST") {
                    return response()->json([
                         "status"=>"200",
                        "message" => "OTP sent successfully " . $otp,
                        "mobileNumber" => $r->mobileNumber,
                        "otp" => $otp,
                        "resetToken"=>$token
                    ], 200);
                }
            } else {
                return response()->json(["status"=>"300",'message' => 'No users were found with the mobile number provided!'], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(["status"=>"300",'message' => $ex->getMessage()], 400);
        }
    }


    public function generateOTP($contactNumber)
    {
        $otp =  $this->randomOTP(4);
        try {
            $result = CustomerOtp::create([
                'mobile' => $contactNumber,
                'otp' => $otp,
                'expired_at' => now()->addMinutes(10)
            ]);
            return $result->otp;
        } catch (Exception $e) {
            return false;
        }
    }

    public function checkRegisterOTP($otp, $contactNumber)
    {
        $query = CustomerOtp::where('mobile', $contactNumber)
            ->where('otp', $otp);
        $result = $query->first();

        if (!empty($result)) {
            if ($result->expired_at < now()) {
                // Delete expired OTP
                CustomerOtp::where('mobile', $contactNumber)->delete();
                return 'expired';
            }
            // OTP is valid, delete it
            CustomerOtp::where('mobile', $contactNumber)->delete();
            return true;
        }

        return false;
    }


    public function resend_otp(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'mobileNumber' => ['required', 'numeric']
            ], [
                'mobileNumber.required' => 'Mobile number is required',
                'mobileNumber.numeric' => 'Mobile number is invalid'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status"=>"300",
                    "message" => $validator->errors()->first()
                ], 500);
            }

            $customer = Customer::where('mobileNumber', $r->mobileNumber)
                       ->where("isActive",1)
                       ->where("isDelete",0)
                       ->first();

            if (empty($customer)) {
                return response()->json(["status"=>"300",'message' => 'No users were found with the mobile number provided!'], 200);
            }

            // Delete existing OTP
            CustomerOtp::where('mobile', $r->mobileNumber)->delete();

            // Generate new OTP
            $otp = $this->generateOTP($r->mobileNumber);

            if ($otp === false) {
                return response()->json(["status"=>"300","message" => "Failed to generate OTP. Please try again."], 200);
            }

            $smsTemplate = SmsTemplate::where("id", 6)->first();

            // Replace placeholders in template with actual values
            $message = str_replace(
                ['{otp}'],
                [$otp],
                $smsTemplate->template
            );

            // If LIVE → send SMS using Twilio
            if (env('SMS_MODE') === "LIVE") {

                $send = (new \App\Classes\Twilio)->sendMessage(
                    $message,
                    $r->mobileNumber
                );

                if ($send === false) {
                    return response()->json([
                         "status"=>"300",
                        "message" => "Failed to send OTP SMS. Please try again."
                    ], 200);
                }
            }

            // Prepare Response
            if (env('SMS_MODE') === "TEST") {
                // Do NOT send SMS in test mode
                $responseData = [
                     "status"=>"200",
                    "message" => "OTP sent successfully. " . $otp,
                    "mobileNumber" => $r->mobileNumber,
                    "otp" => $otp
                ];
            } else {
                // LIVE mode
                $responseData = [
                     "status"=>"200",
                    "message" => "OTP sent successfully",
                    "mobileNumber" => $r->mobileNumber,
                    "otp" => $otp // return OTP in LIVE? remove if not needed
                ];
            }

            return response()->json($responseData, 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }


     // Verify OTP API
    public function verify_otp(Request $r)
    {
        try {
            $input = $r->all();

            $rules = [
                'mobileNumber' => ['required'],
                'otp' => ['required', 'numeric'],
                'token'=>'required'
            ];

            $messages = [
                'mobileNumber.required' => 'Mobile number is required',
                'mobileNumber.numeric' => 'Mobile number is invalid',
                'otp.required' => 'OTP is required',
                'otp.numeric' => 'OTP is invalid',
                'token.required'=>'Token is required.',
            ];

            $validator = Validator::make($input, $rules, $messages);

            if ($validator->fails()) {
                $response = [
                    "status"=>"300",
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $customer = Customer::where('resetToken', $r->token)->first();
            if(empty($customer))
            {
                return response()->json([ "status"=>"300","message" => "Password Reset Link is Expired. Please Forgot Password Again to Continue."], 400);
            }

            // Verify OTP using the existing function
            $result = $this->checkRegisterOTP($r->otp, $r->mobileNumber);

            if ($result === 'expired') {
                return response()->json(["status"=>"300","message" => "OTP has expired. Please request a new one."], 200);
            }

            if ($result === false) {
                return response()->json(["status"=>"300","message" => "Invalid OTP. Please try again."], 200);
            }

            // OTP verified successfully
            // You can perform additional actions here like creating session, token, etc.

            return response()->json([
                "status"=>"200",
                "message" => "OTP verified successfully",
                "mobileNumber" => $r->mobileNumber,
                "verified" => true,
                "token"=>$customer->resetToken
            ], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }


 /*   public function verify_customer_token(Request $r)
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
            $customer = Customer::where('resetToken', $r->token)->first();
            if ($customer) {
                $data = ["resetToken" => $customer->resetToken];
                return response()->json(["message" => "Data found", "data" => $data], 200);
            }
            return response()->json(["message" => "Password Reset Link is Expired. Please Forgot Password Again to Continue."], 400);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }*/

    public function update_customer_password(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'token' => 'required',
                'password' => 'min:6|max:20|confirmed|required|regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{6,20}$/',
                'password_confirmation' => 'min:6|max:20|required|regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{6,20}$/',
            ], [

                'token.required' => 'The token field is required.',
                'password.required' => 'The password field is required.',
                'password.min' => 'The password must be at least 6 characters.',
                'password.max' => 'The password may not be greater than 20 characters.',
                'password.confirmed' => 'The password confirmation does not match.',
                'password.regex' => 'The password must contain at least one digit, one lowercase letter, and one uppercase letter.',
                'password_confirmation.required' => 'The password confirmation field is required.',
                'password_confirmation.min' => 'The password confirmation must be at least 6 characters.',
                'password_confirmation.max' => 'The password confirmation may not be greater than 20 characters.',
                'password_confirmation.regex' => 'The password confirmation must contain at least one digit, one lowercase letter, and one uppercase letter.',
            ]);

            if ($validator->fails()) {
                $response = [
                    "status"=>"300",
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
            }

            $customer = Customer::where('resetToken', $r->token)
                ->first();
            if ($customer) {
                $customer->password = Hash::make($r->password);
                $customer->resetToken = null;
                $customer->save();
                return response()->json(["status"=>"200","message" => "Password updated successful. Please login to continue."], 200);
            }
            return response()->json(["status"=>"300","message" => "Password update is failed . Please Forgot Password Again to Continue."], 400);
        } catch (\Exception $ex) {
            return response()->json(["status"=>"400",'message' => $ex->getMessage()], 400);
        }
    }

    public function change_password(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'customerCode' => 'required',
                'password' => 'min:6|max:20|confirmed|required',
                'password_confirmation' => 'min:6|max:20|required',
            ]);
            if ($validator->fails()) {
                $response = [
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 200);
            }

            $customer = Customer::where('code', $r->customerCode)->first();
            if ($customer) {
                $customer->password =    Hash::make($r->password);
                $customer->save();
                return response()->json(['status' => 200, "message" => "Password updated successful. Please login to continue."], 200);
            }
            return response()->json(['status' => 300, "message" => "Customer not found"], 200);
        } catch (\Exception $ex) {
            return response()->json(['status' => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function customer_profile($code)
    {
        try {
            $result = Customer::where('code', $code)->first();
            if ($result) {
                $path = url("uploads/profile/user.png");
                if ($result->profilePhoto != "" && $result->profilePhoto != null) {
                    $path = url("uploads/customer/" . $result->profilePhoto);
                }
                $data['profilePhoto'] = $path;
                $data['customerCode'] = $result->code;
                $data['firstName'] = $result->firstName ?? "";
                $data['lastName'] = $result->lastName ?? "";
                $data['fullName'] = $result->fullName ?? "";
                $data['mobileNumber'] = $result->mobileNumber ?? "";
                $data['email'] = $result->email ?? "";
                $data['isActive'] = $result->isActive;
                return response()->json(['status' => 200, "message" => "Profile Found", "data" => $data], 200);
            } else {
                return response()->json(['status' => 300, "message" => "Customer not found"], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(['status' => 400, 'message' => $ex->getMessage()], 400);
        }
    }



    public function get_customer_details(String $customerCode, String $mobileNumber, String $email, String $type)
    {
        $query = DB::table("customer");
        if ($type == "1") {
            $query->where("customer.code", $customerCode);
        } else if ($type == "2") {
            $query->where("customer.mobileNumber", $mobileNumber);
        } else {
            $query->where("customer.email", $email);
        }
        $query->where("isActive", 1);
        $result = $query->first();
        if (!empty($result)) {
            $path = "";
            if ($result->profilePhoto != "" && $result->profilePhoto != null) {
                $path = url("uploads/customer/" . $result->profilePhoto);
            }
            $data['profilePhoto'] = $path;
            $data['customerCode'] = $result->code;
            $data['firstName'] = $result->firstName ?? "";
            $data['lastName'] = $result->lastName ?? "";
            $data['fullName'] = $result->fullName ?? "";
            $data['mobileNumber'] = $result->mobileNumber ?? "";
            $data['email'] = $result->email ?? "";
            $data['isActive'] = $result->isActive;
            return $data;
        }
        return false;
    }

    public function customer_logout(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'customerCode' => 'required'
            ]);
            if ($validator->fails()) {
                $response = [
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 500);
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
                return response()->json(["message" => "Logout successfully"], 200);
            } else {
                return response()->json(["message" => "Customer not found"], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function customer_details_by_token(Request $r)
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
            [$id, $customer_token] = explode('|', $r->token, 2);
            $getCustomerDetails = PersonalAccessToken::where('token', hash('sha256', $customer_token))->first();

            if (!empty($getCustomerDetails)) {
                $getCustomer = Customer::select('customer.*')
                    ->where("customer.id", $getCustomerDetails->tokenable_id)
                    ->first();
                if (!empty($getCustomer)) {
                    $path = "";
                    if ($getCustomer->profilePhoto != "" && $getCustomer->profilePhoto != null) {
                        $path = url("uploads/customer/" . $getCustomer->profilePhoto);
                    }
                    $data['code'] = $getCustomer->code;
                    $data['customerName'] = $getCustomer->customerName ?? "";
                    $data['mobileNumber'] = $getCustomer->mobileNumber ?? "";
                    $data['email'] = $getCustomer->email ?? "";
                    $data['isActive'] = $getCustomer->isActive;
                    $data['firebaseId'] = $getCustomer->firebase_id ?? "";
                    $data['profilePhoto'] = $path;
                    return response()->json(["message" => "Customer found", "data" => $data], 200);
                }
                return response()->json(["message" => "Customer not found"], 200);
            }
            return response()->json(["message" => "Invalid Token"], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function getStoreLocationByCity()
    {
        try {
            $storeLocationArray = [];

            // Retrieve store locations grouped by city
            $storeLocations = Storelocation::select('city')
                ->where('isActive', 1)
                ->groupBy('city')
                ->orderBy("city", "ASC")
                ->get();

            if ($storeLocations->isNotEmpty()) {
                foreach ($storeLocations as $storeLocation) {
                    // Create an array of store location data for each city
                    $data = [
                        "city" => $storeLocation->city,
                        "storeLocations" => []
                    ];

                    // Retrieve store locations for the current city
                    $locations = Storelocation::where('city', $storeLocation->city)
                        ->where('isActive', 1)
                        ->orderBy("storeLocation", "ASC")
                        ->get();

                    // Add store location details to the data array
                    foreach ($locations as $item) {
                        $storeData = [
                            "code" => $item->code,
                            "storeLocation" => $item->storeLocation,
                            "storeAddress" => $item->storeAddress,
                            "latitude" => $item->latitude,
                            "longitude" => $item->longitude
                        ];
                        $data['storeLocations'][] = $storeData;
                    }

                    // Add the data for the current city to the array
                    $storeLocationArray[] = $data;
                }

                return response()->json(["message" => "Data found", "data" => $storeLocationArray], 200);
            } else {
                return response()->json(["message" => "No Data found"], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }
}
