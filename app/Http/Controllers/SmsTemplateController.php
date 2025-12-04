<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use DB;

class SmsTemplateController extends Controller
{
    private $role, $rights;

    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('14.1', $this->role);
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
            return view('sms-templates.list', $data);
        } else {
            return view('noright');
        }
    }


    public function getSmsTemplateList(Request $req)
    {
        $search = $req->input('search.value');
        $tableName = "sms_templates";
        $orderColumns = array("sms_templates.*");
        $condition = array();
        $orderBy = array('sms_templates.id' => 'DESC');
        $join = array();
        $like = array('sms_templates.title' => $search, 'sms_templates.template' => $search);

        // Handle DataTables All (-1)
        $limit = $req->length == -1 ? '' : $req->length;
        $offset = $req->length == -1 ? '' : $req->start;

        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);

        $srno = $_GET['start'] + 1;
        $data = array();

        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
                $status = $row->isActive == 1
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">InActive</span>';

                $actions = '<div class="btn-group">
                <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown">
                    <i class="ti-settings"></i>
                </button>
                <div class="dropdown-menu animated slideInUp">';

                if ($this->rights['view'] ?? false)
                    $actions .= '<a class="dropdown-item" href="' . url("sms-templates/view/" . $row->id) . '"><i class="ti-eye mr-2"></i> Open</a>';

                if ($this->rights['update'] ?? false)
                    $actions .= '<a class="dropdown-item" href="' . url("sms-templates/edit/" . $row->id) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';

                if ($this->rights['delete'] ?? false)
                    $actions .= '<a class="dropdown-item delete-template" href="javascript:void(0)" data-id="' . $row->id . '"><i class="fas fa-trash mr-2"></i> Delete</a>';

                $actions .= '</div></div>';

                // Truncate template for display
                $templatePreview = strlen($row->template) > 50
                    ? substr($row->template, 0, 50) . '...'
                    : $row->template;

                $data[] = [$srno, $actions, $row->title, $templatePreview, $status];
                $srno++;
            }
        }

        // Count full data for datatable
        $dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, '', ''));

        echo json_encode([
            "draw" => intval($_GET["draw"]),
            "recordsTotal" => $dataCount,
            "recordsFiltered" => $dataCount,
            "data" => $data
        ]);
    }


    public function add()
    {
        if ($this->rights != '' && $this->rights['insert'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            return view('sms-templates.add', $data);
        } else {
            return view('noright');
        }
    }


    public function store(Request $r)
    {
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];

        $rules = [
            'title' => 'required|min:2|max:255',
            'template' => 'required|min:10',
        ];

        $messages = [
            'title.required' => 'Title is required',
            'title.min' => 'Minimum of 2 characters are required.',
            'title.max' => 'Maximum of 255 characters are allowed.',
            'template.required' => 'Template is required',
            'template.min' => 'Minimum of 10 characters are required.',
        ];

        $this->validate($r, $rules, $messages);

        $smsTemplate = new SmsTemplate();
        $smsTemplate->title = ucwords(strtolower($r->title));
        $smsTemplate->template = $r->template;
        $smsTemplate->isActive = 1;
        $smsTemplate->created_at = $currentdate;

        if (!$smsTemplate->save()) {
            return back()->with('error', 'Failed to add the SMS template');
        }

        // Activity log
        $logData = $currentdate->toDateTimeString() . "\t" . $ip . "\t" .
            Auth::guard('admin')->user()->code . "\tSMS Template " . $smsTemplate->id . " is added.";

        $this->model->activity_log($logData);

        return redirect('sms-templates/list')->with('success', 'SMS Template added successfully');
    }


    public function edit(Request $r, $id)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $data['queryresult'] = SmsTemplate::where('id', $id)->first();

            if (!$data['queryresult']) {
                return redirect('sms-templates/list')->with('error', 'SMS Template not found');
            }

            return view('sms-templates.edit', $data);
        } else {
            return view('noright');
        }
    }


    public function update(Request $r)
    {
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];

        $rules = [
            'id' => 'required',
            'title' => 'required|min:2|max:255',
            'template' => 'required|min:10',
        ];

        $messages = [
            'id.required' => 'ID is required.',
            'title.required' => 'Title is required',
            'title.min' => 'Minimum of 2 characters are required.',
            'title.max' => 'Maximum of 255 characters are allowed.',
            'template.required' => 'Template is required',
            'template.min' => 'Minimum of 10 characters are required.',
        ];

        $this->validate($r, $rules, $messages);

        $smsTemplate = SmsTemplate::find($r->id);
        if (!$smsTemplate) {
            return back()->with('error', 'SMS Template not found');
        }

        $smsTemplate->title = ucwords(strtolower($r->title));
        $smsTemplate->template = $r->template;
        $smsTemplate->isActive = $r->isActive ?? 1;
        $smsTemplate->updated_at = $currentdate;

        if (!$smsTemplate->save()) {
            return back()->with('error', 'Failed to update the SMS template');
        }

        // Activity log
        $logData = $currentdate->toDateTimeString() . "\t" . $ip . "\t" .
            Auth::guard('admin')->user()->code . "\tSMS Template " . $smsTemplate->id . " is updated.";

        $this->model->activity_log($logData);

        return redirect('sms-templates/list')->with('success', 'SMS Template updated successfully');
    }


    public function view(Request $r, $id)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            $data['queryresult'] = SmsTemplate::where('id', $id)->first();

            if (!$data['queryresult']) {
                return redirect('sms-templates/list')->with('error', 'SMS Template not found');
            }

            return view('sms-templates.view', $data);
        } else {
            return view('noright');
        }
    }


    public function delete($id = "")
    {
        $currentDate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];

        if ($id != "") {
            $smsTemplate = SmsTemplate::find($id);

            if (!$smsTemplate) {
                return response()->json(['message' => 'SMS Template not found.', 'status' => 404], 404);
            }

            $smsTemplate->delete();

            $logData = $currentDate->toDateTimeString() . "\t" . $ip . "\t" .
                Auth::guard('admin')->user()->code . "\tSMS Template " . $id . " is deleted.";
            $this->model->activity_log($logData);

            return response()->json(['message' => 'Record deleted successfully.', 'status' => 200], 200);
        }

        return response()->json(['message' => 'Invalid request.', 'status' => 400], 400);
    }
}
