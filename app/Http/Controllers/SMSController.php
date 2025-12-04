<?php

namespace App\Http\Controllers;

use App\Models\TwilioSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\GlobalModel;
use Carbon\Carbon;

use App\Models\SmsTemplate;
use App\Models\SMSLog;
use App\Models\Customer;

class SMSController extends Controller
{
    private $role, $rights;

    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('13.1', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    /**
     * Show SMS send page with Twilio settings
     */
    public function index()
    {
        $data['customers'] = \App\Models\Customer::where('isActive', 1)
            ->where('isDelete', 0)
            ->get();

        $data['sentSms'] = []; // Add your SMS history logic here

        // Get current Twilio settings
        $data['twilioSettings'] = TwilioSetting::getActiveSettings();

        return view('sms.list', $data);
    }

    /**
     * Save or Update Twilio Settings
     * Developer: [Your Name], Date: [Current Date]
     */
    public function saveTwilioSettings(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'twilio_sid' => 'required|string|min:30|max:100',
            'twilio_auth_token' => 'required|string|min:30|max:100',
            'twilio_from_number' => 'required|string|min:10|max:20|regex:/^\+?[1-9]\d{1,14}$/',
        ], [
            'twilio_sid.required' => 'Twilio SID is required',
            'twilio_sid.min' => 'Twilio SID must be at least 30 characters',
            'twilio_auth_token.required' => 'Twilio Auth Token is required',
            'twilio_auth_token.min' => 'Auth Token must be at least 30 characters',
            'twilio_from_number.required' => 'Twilio Phone Number is required',
            'twilio_from_number.regex' => 'Invalid phone number format (use +1234567890)',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $currentDate = Carbon::now();
            $ip = $_SERVER['REMOTE_ADDR'];
            $userCode = Auth::guard('admin')->user()->code ?? 'SYSTEM';

            // Check if settings already exist
            $twilioSetting = TwilioSetting::where('isDelete', 0)->first();

            if ($twilioSetting) {
                // Update existing settings
                $twilioSetting->twilio_session_id = $request->twilio_sid;
                $twilioSetting->twilio_auth_id = $request->twilio_auth_token;
                $twilioSetting->twilio_number = $request->twilio_from_number;
                $twilioSetting->isActive = 1;
                $twilioSetting->updated_at = $currentDate;
                $twilioSetting->save();

                $action = 'updated';
            } else {
                // Create new settings
                $twilioSetting = TwilioSetting::create([
                    'twilio_session_id' => $request->twilio_sid,
                    'twilio_auth_id' => $request->twilio_auth_token,
                    'twilio_number' => $request->twilio_from_number,
                    'isActive' => 1,
                    'isDelete' => 0,
                    'created_at' => $currentDate,
                ]);

                $action = 'added';
            }

            // Activity Log
            $logData = $currentDate->toDateTimeString() . "\t" . $ip . "\t" .
                $userCode . "\tTwilio Settings " . $action . " successfully.";
            $this->model->activity_log($logData);

            return back()->with('success', 'Twilio Settings saved successfully!');
        } catch (\Exception $e) {
            \Log::error('Twilio Settings Save Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to save settings. Please try again.');
        }
    }

    /**
     * Get Twilio Settings (AJAX)
     */
    public function getTwilioSettings()
    {
        try {
            $settings = TwilioSetting::getActiveSettings();

            if ($settings) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'twilio_sid' => $settings->twilio_sid,
                        'twilio_auth_token' => $settings->twilio_auth_token,
                        'twilio_from_number' => $settings->twilio_from_number,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No settings found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get Templates for Select2 (with limit/offset and exclusion)
     */
    public function getTemplates(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $limit = $request->get('limit', 10);
            $offset = $request->get('offset', 0);
            $excludeIds = $request->get('exclude_ids', []); // [1,2,3,4]

            $query = SmsTemplate::where('isActive', 1)
                ->where('isDelete', 0);

            // Exclude specific IDs
            if (!empty($excludeIds)) {
                $query->whereNotIn('id', $excludeIds);
            }

            // Search functionality
            if (!empty($search)) {
                $query->where('template', 'LIKE', '%' . $search . '%');
            }

            $total = $query->count();

            $templates = $query->offset($offset)
                ->limit($limit)
                ->get(['id', 'title', 'template']);

            return response()->json([
                'success' => true,
                'data' => $templates,
                'total' => $total
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Single Template Preview
     */
    public function getTemplatePreview($templateId)
    {
        try {
            $template = SmsTemplate::where('id', $templateId)
                ->where('isActive', 1)
                ->where('isDelete', 0)
                ->first(['title', 'template']);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $template->title,
                    'message' => $template->template
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sendSMS(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Get Template
            $template = SmsTemplate::findOrFail($request->template);

            $currentDate = Carbon::now();
            $userCode = Auth::guard('admin')->user()->code ?? 'SYSTEM';
            $pendingCount = 0;

            // Fetch customers in chunk of 100
            Customer::where('isActive', 1)
                ->where('isDelete', 0)
                ->whereNotNull('mobileNumber')
                ->where('mobileNumber', '!=', '')
                ->select('id', 'mobileNumber')
                ->chunk(100, function ($customers) use ($template, $currentDate, $userCode, &$pendingCount) {

                    $batchInsertData = [];

                    foreach ($customers as $customer) {
                        $batchInsertData[] = [
                            'template_id'       => $template->id,
                            'template_message'  => $template->template,
                            'mobile_number'     => $customer->mobileNumber,
                            'customer_id'       => $customer->id,
                            'status'            => 'pending',
                            'message_response'  => null,
                            'sent_at'           => null,
                            'created_at'        => $currentDate,
                            'updated_at'        => $currentDate,
                        ];

                        $pendingCount++;
                    }

                    // Insert 100 rows at once
                    \DB::table('sms_logs')->insert($batchInsertData);
                });

            // Activity Log
            $logData = $currentDate->toDateTimeString() . "\t" . $_SERVER['REMOTE_ADDR'] . "\t" .
                $userCode . "\tSMS Queue Created: {$pendingCount} pending entries added.";
            $this->model->activity_log($logData);

            return back()->with('success', "{$pendingCount} SMS queued successfully.");
        } catch (\Exception $e) {
            \Log::error('SMS Queue Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to queue SMS: ' . $e->getMessage());
        }
    }



    public function validateSMS(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template' => 'required',
        ], [
            'template.required' => 'Please select a template',
            'template.exists' => 'Selected template is invalid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if template exists and is active
            $template = SmsTemplate::where('id', $request->template)
                ->where('isActive', 1)
                ->where('isDelete', 0)
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found or inactive'
                ], 404);
            }

            // Get customer count
            $customerCount = Customer::where('isActive', 1)
                ->where('isDelete', 0)
                ->whereNotNull('mobileNumber')
                ->where('mobileNumber', '!=', '')
                ->count();

            if ($customerCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active customers found with mobile numbers'
                ], 400);
            }

            // Check Twilio settings
            $twilioSettings = TwilioSetting::getActiveSettings();
            if (!$twilioSettings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Twilio settings not configured. Please configure in Settings tab.'
                ], 400);
            }

            // ✅ All validations passed
            return response()->json([
                'success' => true,
                'customer_count' => $customerCount,
                'message' => 'Validation successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getSmsLogs(Request $req)
    {
        $search = $req->input('search.value');

        $tableName = "sms_logs";

        // Only sms_logs table fields
        $orderColumns = ["sms_logs.*"];

        $join = []; // ❌ No join required

        $condition = [];

        $orderBy = ['sms_logs.id' => 'DESC'];

        // Search only inside sms_logs table
        $like = [
            'sms_logs.mobile_number'    => $search,
            'sms_logs.template_message' => $search,
            'sms_logs.status'           => $search
        ];

        $limit  = $req->length == -1 ? '' : $req->length;
        $offset = $req->length == -1 ? '' : $req->start;

        // Main Query
        $result = $this->model->selectQuery(
            $orderColumns,
            $tableName,
            $join,
            $condition,
            $orderBy,
            $like,
            $limit,
            $offset
        );

        $srno = $_GET['start'] + 1;
        $data = [];

        if ($result && $result->count() > 0) {
            foreach ($result as $row) {

                // Badge
                if ($row->status == "sent") {
                    $status = '<span class="badge badge-success">Sent</span>';
                } elseif ($row->status == "pending") {
                    $status = '<span class="badge badge-warning">Pending</span>';
                } else {
                    $status = '<span class="badge badge-danger">Failed</span>';
                }

                // Message short
                $msg = strlen($row->template_message) > 50
                    ? substr($row->template_message, 0, 50) . "..."
                    : $row->template_message;

                $data[] = [

                    $row->mobile_number,
                    $msg,
                    $status,
                    $row->sent_at ? date("d-m-Y h:i A", strtotime($row->sent_at)) : "-"
                ];

                $srno++;
            }
        }

        // Count Without Limit for DataTable
        $dataCount = sizeof(
            $this->model->selectQuery(
                $orderColumns,
                $tableName,
                [],
                $condition,
                $orderBy,
                $like,
                '',
                ''
            )
        );

        return response()->json([
            "draw"            => intval($_GET["draw"]),
            "recordsTotal"    => $dataCount,
            "recordsFiltered" => $dataCount,
            "data"            => $data
        ]);
    }
}
