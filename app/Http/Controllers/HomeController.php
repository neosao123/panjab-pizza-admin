<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Toastr;

class HomeController extends Controller
{
    public function index()
    {
        return view('index');
    }

    //
    public function accessdenied()
    {
        return view('noright');
    }

    public function test_toastr()
    {
        Toastr::success('Messages in here', 'Title', ["positionClass" => "toast-top-right"]);
        return redirect('/');
    }
	
	public function delete_user_process(Request $r){
		 // Render the Blade view as HTML
        $html = view('sample')->render();

        // Return the HTML content with proper header
        return response($html)->header('Content-Type', 'text/html');
	}
}
