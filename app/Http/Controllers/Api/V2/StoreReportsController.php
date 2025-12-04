<?php

namespace App\Http\Controllers\Api\V2;

use App\Exports\STR_1;
use App\Exports\STR_2;
use App\Exports\STR_3;

use App\Http\Controllers\Controller;
use App\Mail\DailyStoreSummmary;
use App\Models\GlobalModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\OrderMaster;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExcelAttachmentEmail;


class StoreReportsController extends Controller
{
    // Developer: Shreyas Mahamuni, Working Date: 30-11-2023
    private $role, $rights;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }

    // Developer: Shreyas Mahamuni, Working Date: 30-11-2023
    // ----> This Function store excel files into "storage/app/public" folder
    public function downloadReports()
    {
        $now = date('Y-m-d');
        $STR_1 = Excel::store(new STR_1, 'STR_1_' . $now . '.xlsx', 'public');
        $STR_2 = Excel::store(new STR_2, 'STR_2_' . $now . '.xlsx', 'public');
        $STR_3 = Excel::store(new STR_3, 'STR_3_' . $now . '.xlsx', 'public');
        return response()->json(["message" => "Data found", $STR_1, $STR_2, $STR_3], 200);
    }

    // Developer: Shreyas Mahamuni, Working Date: 30-11-2023
    // -----> This function send email with store wise reports excel files
    public function sendReportToEmail()
    {
        try {
            Mail::to('testing.neosaoservices@gmail.com')
                ->send(new ExcelAttachmentEmail());
        } catch (\Exception $e) {
            return response()->json(["Exception" =>  $e->getMessage()]);
        }
    }

    public function daily_store_summary_mail()
    {
        try {

            $date = date('Y-m-d', strtotime(' - 1 days'));

            $formatedDate = date('d/m/Y', strtotime(' - 1 days'));

            $query = DB::table('ordermaster as om')
                ->join('storelocation as sl', 'om.storeLocation', '=', 'sl.code')
                ->select(
                    'sl.storeLocation as store_name',
                    DB::raw('COUNT(om.id) as total_orders'),
                    DB::raw('SUM(om.grandTotal) as total_amount'),
                    DB::raw("SUM(CASE WHEN om.orderFrom = 'online' THEN 1 ELSE 0 END) as online_orders"),
                    DB::raw("SUM(CASE WHEN om.orderFrom = 'store' THEN 1 ELSE 0 END) as in_store_orders")
                )
                ->where('om.orderStatus', '!=', 'cancelled')
                ->whereBetween('om.orderDate', ["$date 00:00:00", "$date 23:59:59"]);
            $records = $query->groupBy('sl.storeLocation')->get();
            Mail::to('abhiharshe1191@gmail.com')
                ->send(new DailyStoreSummmary($records, $formatedDate));
        } catch (\Exception $e) {
            return response()->json(["Exception" =>  $e->getMessage()]);
        }
    }
}
