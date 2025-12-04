<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
// Models
use App\Models\Customer;
use App\Models\Customeraddress;

use App\Models\BackgroundImage;


class BackgroundImageController extends Controller
{
    public function getBackgroundImage(Request $request)
    {
        $image = BackgroundImage::first();

        if (!$image || empty($image->image_path)) {
            return response()->json([
                'status' => 404,
                'message' => 'No background image found',
                'image_path' => ''
            ], 404);
        }

        $path = $image->image_path;

        // Ensure it's a string
        if (!is_string($path)) {
            return response()->json([
                'status' => 500,
                'message' => 'Invalid image path format',
                'image_path' => ''
            ], 500);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Background image found',
            'image_path' => url($path) . '?v=' . time()
        ], 200);
    }
}
