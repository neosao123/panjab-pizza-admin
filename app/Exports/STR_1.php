<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;
use App\Models\OrderMaster;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class STR_1 implements FromView, ShouldAutoSize
{
    // Developer: Shreyas Mahamuni, Working Date: 30-11-2023
    // ----> This Function return view for excel file with orders in between todaysDate and previous 24 hrs for STR_1
    public function view(): View
    {
        $now = date('Y-m-d 4:15:00');
        $prev24Hours = date('Y-m-d 4:15:00', strtotime('- 1 days'));

        return view('exports.STR_1', [
            'storeLocation' => DB::table('storelocation')->where('code', 'STR_1')->first(),
            'orders' => OrderMaster::whereBetween('created_at', [$prev24Hours, $now])->where('storeLocation', 'STR_1')->get()
        ]);
    }
}
