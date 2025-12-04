<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\Picture;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Specialoffer;
use App\Models\SignaturePizza;
use App\Models\SidesMaster;
use App\Models\Pizzas;
use App\Models\Sides;

class PictureController extends Controller
{
    private $role, $rights, $model;

    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('3.9', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }


    // Get products based on category type
    public function get_products(Request $request)
    {
        $categoryType = $request->category_type;
        $products = [];

        try {
            switch ($categoryType) {
                case 'special_offers':
                    $records = Specialoffer::select("code", "name")
                        ->whereNotNull("pizza_prices")
                        ->where("isActive", 1)
                        ->where("showOnClient", 1)
                        ->where(function ($query) {
                            $query->whereNull("limited_offer")
                                ->orWhere("limited_offer", 0);
                        })
                        ->orderBy('name', 'ASC')
                        ->get();

                    foreach ($records as $item) {
                        $products[] = [
                            'code' => $item->code,
                            'name' => $item->name
                        ];
                    }
                    break;

                case 'signature_pizzas':
                    $records = SignaturePizza::select("code", "pizza_name")
                        ->where("isActive", 1)
                        ->orderBy('pizza_name', 'ASC')
                        ->get();

                    foreach ($records as $item) {
                        $products[] = [
                            'code' => $item->code,
                            'name' => $item->pizza_name
                        ];
                    }
                    break;

                case 'other_pizzas':
                    $records = Pizzas::select("code", "pizza_name")
                        ->where("isActive", 1)
                        ->orderBy('pizza_name', 'ASC')
                        ->get();

                    foreach ($records as $item) {
                        $products[] = [
                            'code' => $item->code,
                            'name' => $item->pizza_name
                        ];
                    }
                    break;

                case 'sides':
                    $records = SidesMaster::select("code", "sidename")
                        ->where('isActive', 1)
                        ->orderBy('sidename', 'ASC')
                        ->get();

                    foreach ($records as $item) {
                        $products[] = [
                            'code' => $item->code,
                            'name' => $item->sidename
                        ];
                    }
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid category type'
                    ]);
            }

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching products: ' . $e->getMessage()
            ]);
        }
    }

    // Show list page
    public function index()
    {
        if ($this->rights != '' && $this->rights['view'] == 1) {
            $pictureCount = Picture::where('isDelete', 0)->count();
            $rights = $this->rights;
            return view('picture.list', compact('pictureCount', 'rights'));
        } else {
            return view('noright');
        }
    }

    // Fetch list data (for DataTables)
    public function getPictureList(Request $req)
    {
        $search = $req->input('search.value');
        $tableName = "picture";
        $orderColumns = ["picture.*"];
        $condition = ['picture.isDelete' => ['=', 0]];
        $orderBy = ['picture.id' => 'DESC'];
        $like = ['picture.title' => $search, 'picture.product_url' => $search];
        $limit = $req->length;
        $offset = $req->start;

        $result = $this->model->selectQuery($orderColumns, $tableName, [], $condition, $orderBy, $like, $limit, $offset);

        $srno = $_GET['start'] + 1;
        $data = [];
        $dataCount = 0;

        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
                $status = $row->isActive == 1
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';

                $image = $row->image ? '<img src="' . url("uploads/picture/" . $row->image) . "?v=" . time() . '" height="50" width="50">' : '';

                $actions = '<div class="btn-group">
                    <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown">
                        <i class="ti-settings"></i>
                    </button>
                    <div class="dropdown-menu animated slideInUp">';

                if ($this->rights['view'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("pictures/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> View</a>';
                }
                if ($this->rights['update'] == 1) {
                    $actions .= '<a class="dropdown-item" href="' . url("pictures/edit/" . $row->code) . '"><i class="fas fa-edit mr-2"></i> Edit</a>';
                }
                if ($this->rights['delete'] == 1) {
                    $actions .= '<a class="dropdown-item delbtn" data-id="' . $row->code . '"><i class="ti-trash mr-2"></i> Delete</a>';
                }

                $actions .= '</div></div>';

                $data[] = [
                    $srno,
                    $actions,
                    $row->title,
                    $row->product_url ?? '-',
                    $image,
                    $status,
                ];
                $srno++;
            }

            $dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName, [], $condition, $orderBy, $like, '', ''));
        }

        $output = [
            "draw" => intval($_GET["draw"]),
            "recordsTotal" => $dataCount,
            "recordsFiltered" => $dataCount,
            "data" => $data
        ];
        echo json_encode($output);
    }

    // Add page
    public function add()
    {
        $count = Picture::where('isDelete', 0)->count();
        if ($count >= 3) {
            return redirect('/pictures/list')->with('error', 'You can only add up to 3 pictures.');
        }

        if ($this->rights['insert'] == 1) {
            $viewRights = 1;
            return view('picture.add', compact('viewRights'));
        } else {
            return view('noright');
        }
    }

    public function store(Request $r)
    {
        $rules = [
            'title' => 'required|min:3|max:150',
            'pizza_type' => 'required|in:special_offers,signature_pizzas,other_pizzas,sides',
            'product_id' => 'required|string|max:50',
            'product_url' => 'nullable|url|max:255',
            'image' => 'required|image|mimes:jpg,png,jpeg|max:2048|dimensions:min_width=512,min_height=512',
        ];

        $messages = [
            'image.dimensions' => 'The image must be minimum of 512x512 pixels.',
            'pizza_type.required' => 'Pizza type is required.',
            'pizza_type.in' => 'Invalid pizza type selected.',
            'product_id.required' => 'Product is required.',
        ];

        $this->validate($r, $rules, $messages);

        $data = [
            'title' => $r->title,
            'pizza_type' => $r->pizza_type,
            'product_id' => $r->product_id,
            'product_url' => $r->product_url,
            'isActive' => 1,
            'isDelete' => 0,
        ];

        // Handle image upload
        if ($r->hasFile('image')) {
            $file = $r->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/picture'), $filename);
            $data['image'] = $filename;
        }

        $res = $this->model->addNew($data, 'picture', 'PIC');

        if ($res) {
            return redirect('/pictures/list')->with('success', 'Picture added successfully');
        }

        return redirect('/pictures/list')->with('error', 'Failed to add picture');
    }

    // Edit

    public function edit($code)
    {
        $picture = Picture::where('code', $code)->first();

        if ($picture) {
            $viewRights = $this->rights['view'] ?? 0;

            return view('picture.edit', [
                'queryresult' => $picture,
                'viewRights' => $viewRights
            ]);
        }

        return back()->with('error', 'Picture not found');
    }

    // Update
    public function update(Request $r)
    {
        $code = $r->code;

        $rules = [
            'title' => 'required|min:3|max:150',
            'pizza_type' => 'required|in:special_offers,signature_pizzas,other_pizzas,sides',
            'product_id' => 'required|string|max:50',
            'product_url' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,webp|max:2048|dimensions:min_width=512,min_height=512',
        ];

        $messages = [
            'image.dimensions' => 'The image must be minimum of 512x512 pixels.',
            'pizza_type.required' => 'Pizza type is required.',
            'pizza_type.in' => 'Invalid pizza type selected.',
            'product_id.required' => 'Product is required.',
        ];

        $this->validate($r, $rules, $messages);

        // Find the picture
        $picture = Picture::where('code', $code)->first();
        if (!$picture) {
            return back()->with('error', 'Picture not found');
        }

        $picture->title = $r->title;
        $picture->pizza_type = $r->pizza_type;
        $picture->product_id = $r->product_id;
        $picture->product_url = $r->product_url;
        $picture->isActive = $r->isActive ? 1 : 0;

        // Handle new image
        if ($r->hasFile('image')) {
            // Delete old image if exists
            if ($picture->image) {
                $oldImagePath = public_path('uploads/picture/' . $picture->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $file = $r->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/picture'), $filename);
            $picture->image = $filename;
        }

        $picture->save();

        return redirect('/pictures/list')->with('success', 'Picture updated successfully');
    }

    // Delete image
    public function deleteImage(Request $r)
    {
        $code = $r->code;
        $filename = $r->value;
        $picture = Picture::where('code', $code)->first();

        if ($picture && $picture->image == $filename) {
            $filePath = public_path('uploads/picture/' . $filename);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $this->model->doEdit(['image' => null], 'picture', $code);
            return response()->json(true);
        }
        return response()->json(false);
    }

    // Delete
    public function delete(Request $r)
    {
        $code = $r->code;
        $data = [
            'isActive' => 0,
            'isDelete' => 1,
        ];
        $result = $this->model->doEdit($data, 'picture', $code);
        if ($result) {
            return response()->json(["status" => "success"]);
        }
        return response()->json(["status" => "fail"]);
    }

    // View
    public function view(Request $r)
    {
        $code = $r->code;
        $picture = Picture::where('picture.code', $code)->first();
        if ($picture) {
            return view('picture.view', ['queryresult' => $picture]);
        }
        return back()->with('error', 'Picture not found');
    }
}
