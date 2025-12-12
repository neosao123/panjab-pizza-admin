<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\SectionLineentries;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use DB;


class SectionController extends Controller
{
    private $role, $rights;

    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('12.2', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    // Developer: seema, Working Date: 22-11-2025
    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['insertRights'] = $this->rights['insert'];
            return view('sections.list', $data);
        } else {
            return view('noright');
        }
    }

    // Developer: seema, Working Date: 22-11-2025
    public function getSectionList(Request $req)
    {
        $search = $req->input('search.value');
        $tableName = "sections";
        $orderColumns = array("sections.*");
        $condition = array();
        $orderBy = array('sections.id' => 'DESC');
        $join = array();
        $like = array('sections.title' => $search);

        // FIX: Handle DataTables All (-1)
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
                    $actions .= '<a class="dropdown-item" href="' . url("sections/view/" . $row->id) . '"><i class="ti-eye mr-2"></i> Open</a>';

                if ($this->rights['update'] ?? false)
                    $actions .= '<a class="dropdown-item" href="' . url("sections/edit/" . $row->id) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';

           
                $actions .= '</div></div>';

                $data[] = [$srno, $actions, $row->title, $row->subTitle ?? '-', $status];
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


    // Developer: seema, Working Date: 22-11-2025
    public function add()
    {
        if ($this->rights != '' && $this->rights['insert'] == 1) {
            $data['viewRights'] = $this->rights['view'];
            return view('sections.add', $data);
        } else {
            return view('noright');
        }
    }

    // Developer: seema, Working Date: 22-11-2025
    // Developer: seema, Working Date: 22-11-2025
    public function store(Request $r)
    {
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];

        $rules = [
            'title' => 'required|min:2',
            'subTitle' => 'nullable|min:2',
        ];

        $messages = [
            'title.required' => 'Title is required',
            'title.min' => 'Minimum of 2 characters are required.',
        ];

        $this->validate($r, $rules, $messages);

        // ---------------------------
        // SAVE MAIN SECTION
        // ---------------------------
        $section = new Section();
        $section->title = ucwords(strtolower($r->title));
        $section->subTitle = $r->subTitle;
        $section->isActive = 1;
        $section->created_at = $currentdate;

        if (!$section->save()) {
            return back()->with('error', 'Failed to add the section');
        }

        $currentId = $section->id;

        // ---------------------------
        // SAVE LINE ENTRIES
        // ---------------------------
        if ($currentId && isset($r->line_title) && count($r->line_title) > 0) {

            for ($i = 0; $i < count($r->line_title); $i++) {

                $lineEntry = new SectionLineentries();
                $lineEntry->section_id = $currentId;
                $lineEntry->title = $r->line_title[$i];
                $lineEntry->counter = $r->counter[$i];
                $lineEntry->created_at = $currentdate;

                if (!$lineEntry->save()) {
                    return back()->with('error', 'Failed to save section line entry');
                }

                $lineEntryId = $lineEntry->id;

                // ---------------------------
                // IMAGE UPLOAD
                // ---------------------------
                if (isset($r->file('line_image')[$i]) && $r->file('line_image')[$i]) {

                    $file = $r->file('line_image')[$i];
                    $imageName = $lineEntryId . "." . $file->getClientOriginalExtension();

                    $file->move("uploads/section-images", $imageName);

                    // ❗ ONLY CHANGE DONE HERE → Store folder + filename in DB
                    $lineEntry->image = "uploads/section-images/" . $imageName;

                    if (!$lineEntry->save()) {
                        return back()->with('error', 'Failed to upload image for line entry');
                    }
                }

                // ---------------------------
                // ACTIVITY LOG – line entry
                // ---------------------------
                $logData = $currentdate->toDateTimeString() . "\t" . $ip . "\t" .
                    Auth::guard('admin')->user()->code . "\tSection Lineentries " .
                    $currentId . " " . $lineEntryId . " is added.";

                $this->model->activity_log($logData);
            }
        }

        // ---------------------------
        // ACTIVITY LOG – main section
        // ---------------------------
        $logData = $currentdate->toDateTimeString() . "\t" . $ip . "\t" .
            Auth::guard('admin')->user()->code . "\tSection " . $currentId . " is added.";

        $this->model->activity_log($logData);

        return redirect('sections/list')->with('success', 'Section added successfully');
    }

    // Developer: seema, Working Date: 22-11-2025
    public function edit(Request $r, $id)
    {
        if ($this->rights != '' && $this->rights['update'] == 1) {
            $data['viewRights'] = $this->rights['view'];

            $data['queryresult'] = Section::where('id', $id)->first();
            $data['lineentries'] = SectionLineentries::where('section_id', $id)->get();
            return view('sections.edit', $data);
        } else {
            return view('noright');
        }
    }

    // Developer: seema, Working Date: 22-11-2025
    public function update(Request $r)
    {
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];

        $rules = [
            'id' => 'required',
            'title' => 'required|min:2',
            'subTitle' => 'nullable|min:2',
            'line_image.*' => 'nullable|image|mimes:jpg,jpeg,png'
        ];

        $messages = [
            'id.required' => 'ID is required.',
            'title.required' => 'Title is required',
            'title.min' => 'Minimum of 2 characters are required.',
            'line_image.*.image' => 'Only image files are allowed',
            'line_image.*.mimes' => 'Only JPG, JPEG, PNG formats allowed'
        ];

        $this->validate($r, $rules, $messages);

        // --------------------------------------
        // STRICT 300×300 SERVER-SIDE VALIDATION
        // --------------------------------------
        if ($r->hasFile('line_image')) {
            foreach ($r->file('line_image') as $index => $file) {

                if ($file) {
                    $imageInfo = getimagesize($file);

                    if ($imageInfo) {
                        $width = $imageInfo[0];
                        $height = $imageInfo[1];

                        if ($width != 300 || $height != 300) {
                            return back()->with('error', "Image at row " . ($index + 1) . " must be exactly 300px × 300px.")
                                        ->withInput();
                        }
                    }
                }
            }
        }

        // ---------------------------
        // UPDATE MAIN SECTION
        // ---------------------------
        $section = Section::find($r->id);
        if (!$section) {
            return back()->with('error', 'Section not found');
        }

        $section->title = ucwords(strtolower($r->title));
        $section->subTitle = $r->subTitle;
        $section->isActive = 1;
        $section->updated_at = $currentdate;

        if (!$section->save()) {
            return back()->with('error', 'Failed to update the section');
        }

        // ---------------------------
        // UPDATE LINE ENTRIES
        // ---------------------------
        if (isset($r->line_title) && count($r->line_title) > 0) {

            for ($i = 0; $i < count($r->line_title); $i++) {

                // NEW ENTRY
                if ($r->line_id[$i] == "##NA") {

                    $lineEntry = new SectionLineentries();
                    $lineEntry->section_id = $section->id;
                    $lineEntry->title = $r->line_title[$i];
                    $lineEntry->counter = $r->counter[$i] ?? 0;
                    $lineEntry->created_at = $currentdate;
                    $lineEntry->save();

                    $lineEntryId = $lineEntry->id;

                    // IMAGE UPLOAD
                    if (isset($r->file('line_image')[$i]) && $r->file('line_image')[$i]) {

                        $file = $r->file('line_image')[$i];
                        $imageName = $lineEntryId . "." . $file->getClientOriginalExtension();
                        $file->move("uploads/section-images", $imageName);

                        $lineEntry->image = "uploads/section-images/" . $imageName;
                        $lineEntry->save();
                    }
                }

                // EXISTING ENTRY UPDATE
                else {

                    $lineEntry = SectionLineentries::find($r->line_id[$i]);
                    if (!$lineEntry) {
                        continue;
                    }

                    $lineEntry->title = $r->line_title[$i];
                    $lineEntry->counter = $r->counter[$i] ?? 0;
                    $lineEntry->updated_at = $currentdate;

                    // IMAGE UPDATE
                    if (isset($r->file('line_image')[$i]) && $r->file('line_image')[$i]) {

                        $file = $r->file('line_image')[$i];
                        $imageName = $lineEntry->id . "." . $file->getClientOriginalExtension();
                        $file->move("uploads/section-images", $imageName);

                        $lineEntry->image = "uploads/section-images/" . $imageName;
                    }

                    $lineEntry->save();
                }
            }
        }

        // LOG
        $this->model->activity_log(
            $currentdate . "\t" . $ip . "\t" .
            Auth::guard('admin')->user()->code .
            "\tSection " . $section->id . " is updated."
        );

        return redirect('sections/list')->with('success', 'Section updated successfully');
    }


    // Developer: seema, Working Date: 22-11-2025
    // Developer: seema, Working Date: 22-11-2025
    public function deleteImage(Request $r)
    {
        $imgNm = $r->value;
        $id = $r->id;

        // Delete old image from folder
        if ($imgNm && file_exists('uploads/section-images/' . $imgNm)) {
            unlink('uploads/section-images/' . $imgNm);
        }

        // Update EORM
        SectionLineentries::where('id', $id)->update([
            'image' => ''
        ]);

        echo 'true';
    }


    // Developer: Seema, Working Date: 22-11-2025
    public function deleteLineentries($id = "")
    {
        $currentdate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];

        if ($id != "") {

            // Get line entry
            $lineentry = SectionLineentries::find($id);

            if ($lineentry) {

                // Delete image if exists
                if (!empty($lineentry->image) && file_exists('uploads/section-images/' . $lineentry->image)) {
                    unlink('uploads/section-images/' . $lineentry->image);
                }

                // Delete record
                $lineentry->delete();

                // Log entry
                $logData = $currentdate->toDateTimeString() . "\t" . $ip . "\t" .
                    Auth::guard('admin')->user()->code . "\tSection Lineentries ID " . $id . " is deleted.";

                $this->model->activity_log($logData);

                return response()->json(['message' => 'Record deleted successfully.', 'status' => 200], 200);
            }
        }

        return response()->json(['message' => 'Failed to delete.', 'status' => 400], 400);
    }


    // Developer: Seema, Working Date: 22-11-2025
    public function view(Request $r, $id)
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $data['viewRights'] = $this->rights['view'];

            $data['queryresult'] = Section::where('id', $id)->first();
            $data['lineentries'] = SectionLineentries::where('section_id', $id)->get();
            return view('sections.view', $data);
        } else {
            return view('noright');
        }
    }

    // Developer: Seema, Working Date: 22-11-2025
    public function delete($id = "")
    {
        $currentDate = Carbon::now();
        $ip = $_SERVER['REMOTE_ADDR'];

        if ($id != "") {
            // Delete all line entries and their images
            $lineentries = SectionLineentries::where('section_id', $id)->get();
            foreach ($lineentries as $entry) {
                if (!empty($entry->image) && file_exists('uploads/section-images/' . $entry->image)) {
                    unlink('uploads/section-images/' . $entry->image);
                }
            }
            SectionLineentries::where('section_id', $id)->delete();

            // Delete section
            Section::where('id', $id)->delete();

            $logData = $currentDate->toDateTimeString() . "\t" . $ip . "\t" . Auth::guard('admin')->user()->code . "\tSection " . $id . " is deleted.";
            $this->model->activity_log($logData);

            return redirect('sections/list')->with('success', 'Record deleted successfully.');
        }
    }
}
