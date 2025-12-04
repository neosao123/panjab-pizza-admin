<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\ContactUsEmail;
use App\Models\CrustType;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\ApiModel;
use App\Models\Cheese;
use App\Models\Crust;
use App\Models\Dips;
use App\Models\Pizzas;
use App\Models\Softdrinks;
use App\Models\Specialbases;
use App\Models\Toppings;
use App\Models\SidesMaster;
use App\Models\SignaturePizza;
use App\Models\Zipcode;
use App\Models\SidelineEntries;
use App\Models\Storelocation;
use App\Models\Setting;
use App\Models\Sauce;
use App\Models\Spices;
use App\Models\Cook;
use App\Models\DynamicSlider;
use App\Models\DynamicSliderLineentries;
use App\Models\Specialoffer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class TestController extends Controller
{
    public function __construct(GlobalModel $model, ApiModel $apimodel)
    {
        $this->model = $model;
        $this->apimodel = $apimodel;
    }

    public function ingredients(Request $request)
    {
        $current_Date = Carbon::now();

        if ($current_Date > Carbon::parse('2025-11-19 23:59:59')) {
            return response()->json(["status" => "fail", "message" => "This API is no longer available."], 404);
        }

        if (!$request->has('sign_key')) {
            return response()->json(["status" => "fail", "message" => "This API is no longer available."], 404);
        }

        if ($request->has('sign_key') && $request->sign_key != 'akjsh3h28jais1poqpamvg1') {
            return response()->json(["status" => "fail", "message" => "This API is no longer available."], 404);
        }

        try {

            $crustArray = [];
            $crustTypeArray = [];

            $specialbasesArray = [];
            $toppingsArray = [];
            $countAsOne = [];
            $countAsTwo = [];
            $freeToppings = [];

            $crust = Crust::where('isActive', 1)->orderBy("id", "ASC")->limit(3)->get();

            $specialbases = Specialbases::where('isActive', 1)->orderBy("id", "ASC")->limit(3)->get();

            $toppingsone = Toppings::where('isActive', 1)
                ->where("toppings.topping_type", 'regular')
                ->where("toppings.isPaid", 1)
                ->orderBy("id", "ASC")
                ->limit(3)
                ->get();

            $toppingstwo = Toppings::where('isActive', 1)
                ->where("toppings.topping_type", 'non-regular')
                ->where("toppings.isPaid", 1)
                ->orderBy("id", "ASC")
                ->limit(3)
                ->get();

            if ($crust && count($crust) > 0) {
                foreach ($crust as $item) {
                    $data = ["crustCode" => $item->code, "price" => $item->price, "crustName" => $item->crust];
                    array_push($crustArray, $data);
                }
            }

            if ($specialbases && count($specialbases) > 0) {
                foreach ($specialbases as $item) {
                    $data = ["specialbaseCode" => $item->code, "specialbaseName" => $item->specialbase, "price" => $item->price];
                    array_push($specialbasesArray, $data);
                }
            }

            if ($toppingsone && count($toppingsone) > 0) {
                foreach ($toppingsone as $item) {
                    $data = ["toppingsName" => $item->toppingsName,  "countAs" => $item->countAs, "price" => $item->price];
                    array_push($countAsOne, $data);
                }
            }

            if ($toppingstwo && count($toppingstwo) > 0) {
                foreach ($toppingstwo as $item) {
                    $data = ["toppingsName" => $item->toppingsName,  "countAs" => $item->countAs, "price" => $item->price];
                    array_push($countAsTwo, $data);
                }
            }

            $toppingsArray = ["countAsOne" => $countAsOne, "countAsTwo" => $countAsTwo];

            return response()->json([
                "status" => 200,
                "message" => "Data found",
                "data" => [
                    "crust" => $crustArray,
                    "specialbases" => $specialbasesArray,
                    "toppings" => $toppingsArray,
                ]
            ], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function pizzas(Request $request)
    {
        $current_Date = Carbon::now();

        if ($current_Date > Carbon::parse('2025-11-19 23:59:59')) {
            return response()->json(["status" => "fail", "message" => "This API is no longer available."], 404);
        }

        if (!$request->has('sign_key')) {
            return response()->json(["status" => "fail", "message" => "This API is no longer available."], 404);
        }

        if ($request->has('sign_key') && $request->sign_key != 'akjsh3h28jais1poqpamvg1') {
        }

        try {
            $cheeses = Cheese::select('code', 'cheese as cheeseName', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
            $crusts = Crust::select('code', 'crust as crustName', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();


            $records = SignaturePizza::with('category:code,category_name')->whereNotNull("pizza_prices")->where("isActive", 1)->orderBy('id', 'DESC')->limit(6)->get();

            if ($records && count($records) > 0) {
                $signaturePizzas = $records;
                foreach ($signaturePizzas as $item) {

                    unset($item->isActive);
                    unset($item->ratings);
                    unset($item->reviews);
                    unset($item->isDelete);
                    unset($item->addID);
                    unset($item->addIP);
                    unset($item->addDate);
                    unset($item->editIP);
                    unset($item->editID);
                    unset($item->editDate);
                    unset($item->deleteID);
                    unset($item->deleteIP);
                    unset($item->deleteDate);
                    unset($item->description);
                    unset($item->crust);
                    unset($item->crust_type);
                    unset($item->special_base);
                    unset($item->spices);
                    unset($item->sauce);
                    unset($item->cook);
                    unset($item->topping_as_1);
                    unset($item->topping_as_2);
                    unset($item->topping_as_free);
                    unset($item->cheese);

                    $path = url("uploads/pizza.jpg");
                    if ($item->pizza_image != "" && $item->pizza_image != null) {
                        $path = url("uploads/signature-pizza/" . $item->pizza_image);
                    }
                    $item->pizza_image = $path;

                    $item->pizza_description =   $item->pizza_description == null ? "" :  $item->pizza_description;
                }
                return response()->json(["message" => "Data found", "data" => $signaturePizzas], 200);
            }
            return response()->json(["message" => "Data not found"], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }
}
