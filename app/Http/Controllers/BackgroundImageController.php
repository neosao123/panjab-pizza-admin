<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BackgroundImage;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BackgroundImageController extends Controller
{
    private $role, $rights;
    
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('12.1', $this->role);
            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    } 

    // List
    public function index()
    {
        $data['images'] = BackgroundImage::all();
  
        return view('background-image.list', $data);
    }

    // Edit Form
    public function edit($id)
    {
        $data['queryresult'] = BackgroundImage::find($id);
        return view('background-image.edit', $data);
    }

    // Update
    public function update(Request $r)
    {
        $rules = [
            'id' => 'required',
            'image_path' => 'required|image|mimes:jpg,jpeg,png',
        ];
        
        $messages = [
            'image_path.required' => 'Background image is required',
            'image_path.image' => 'Background image must be an image file',
            'image_path.mimes' => 'Background image must be a file of type: jpg, jpeg, png',
        ];
        
        $this->validate($r, $rules, $messages);
        
        $id = $r->id;
        $image = BackgroundImage::find($id);

        if ($filenew = $r->file('image_path')) {
            // Delete old image if exists
            if ($image && $image->image_path && file_exists($image->image_path)) {
                unlink($image->image_path);
            }
            
            // Generate filename: id + extension
            $imagename = $id . "." . $filenew->getClientOriginalExtension();
            
            // Move to folder
            $filenew->move('uploads/background-images', $imagename);
            
            // Save full path in database (folder path + filename)
            $image->image_path = 'uploads/background-images/' . $imagename;
            $image->save();
            
            return redirect('background-image/list')->with('success', 'Background image updated successfully');
        }
        
        return back()->with('error', 'Failed to update background image');
    }

    // Delete Image
    public function deleteImage(Request $r)
    {
        $fullPath = $r->value; // Full path from database
        $id = $r->id;
        
        // Delete from folder
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        
        // Update database - set to null
        $image = BackgroundImage::find($id);
        $image->image_path = null;
        $image->save();
        
        echo 'true';
    }
}