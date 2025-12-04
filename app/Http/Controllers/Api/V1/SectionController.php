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

use App\Models\Section;
use App\Models\SectionLineentries;


class SectionController extends Controller
{
    public function get_sections(Request $request)
    {
        $sections = Section::with(['lineentries' => function ($q) {
            $q->select('id', 'section_id', 'title', 'counter', 'image');
        }])
            ->select('id', 'title', 'subTitle', 'isActive')
            ->where('isActive', 1)
            ->get();

        // -----------------------------
        // If no data found
        // -----------------------------
        if ($sections->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No sections found',
                'data' => []
            ], 404);
        }

        // -----------------------------
        // Add full image URL
        // -----------------------------
        $sections->map(function ($section) {
            $section->lineentries->map(function ($line) {
                $line->image_path = $line->image
                    ? url($line->image). '?v=' . time()
                    : null;
            });
            return $section;
        });
        return response()->json([
            'status' => 200,
            'message' => 'Sections retrieved successfully',
            'data' => $sections
        ], 200);
    }
}
