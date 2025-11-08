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

        if ($current_Date > Carbon::parse('2025-10-15 23:59:59')) {
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


            $crust = Crust::where('isActive', 1)->orderBy("id", "ASC")->get();
            $specialbases = Specialbases::where('isActive', 1)->orderBy("id", "ASC")->get();

            $pizzaPrices = DB::table('pizza_prices')->select('size', 'price')->get();
            $toppingsone = Toppings::where('isActive', 1)
                ->where("toppings.topping_type", 'regular')
                ->where("toppings.isPaid", 1)
                ->orderBy("id", "ASC")
                ->get();
            $toppingstwo = Toppings::where('isActive', 1)
                ->where("toppings.topping_type", 'non-regular')
                ->where("toppings.isPaid", 1)
                ->orderBy("id", "ASC")
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
                    "sizesAndPrices" => $pizzaPrices,
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

        if ($current_Date > Carbon::parse('2025-10-15 23:59:59')) {
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
            $crustTypes = CrustType::select('code', 'crustType', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
            $specialBases = Specialbases::select('code', 'specialbase as specialbaseName', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
            $cooks = Cook::select('code as cookCode', 'cook', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
            $sauces = Sauce::select('code as sauceCode', 'sauce', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
            $spices = Spices::select('code as spicyCode', 'spicy', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();

            $records = SignaturePizza::with('category')->whereNotNull("pizza_prices")->where("isActive", 1)->orderBy('id', 'DESC')->get();

            if ($records && count($records) > 0) {
                $signaturePizzas = $records;
                foreach ($signaturePizzas as $item) {
                    $path = url("uploads/pizza.jpg");
                    if ($item->pizza_image != "" && $item->pizza_image != null) {
                        $path = url("uploads/signature-pizza/" . $item->pizza_image);
                    }
                    $item->pizza_image = $path;

                    $item->pizza_description =   $item->pizza_description == null ? "" :  $item->pizza_description;

                    $cheese = $cheeses->firstWhere('code', $item->cheese['code']);
                    $itemCheese = $item->cheese;
                    $itemCheese['price'] = $cheese ? $cheese['price'] : '0.00';
                    $item->cheese = $itemCheese;

                    $crust = $crusts->firstWhere('code', $item->crust['code']);
                    $itemCrust = $item->crust;
                    $itemCrust['price'] = $crust ? $crust['price'] : '0.00';
                    $item->crust = $itemCrust;

                    $crustType = $crustTypes->firstWhere('code', $item->crust_type['code']);
                    $itemCrustType = $item->crust_type;
                    $itemCrustType['price'] = $crustType ? $crustType['price'] : '0.00';
                    $item->crust_type = $itemCrustType;

                    $specialBase = $specialBases->firstWhere('code', $item->special_base['code']);
                    $itemSpecialBase = $item->special_base;
                    $itemSpecialBase['price'] = $specialBase ? $specialBase['price'] : '0.00';
                    $item->special_base = $itemSpecialBase;

                    $cook = $cooks->firstWhere('code', $item->cook['code']);
                    $itemCook = $item->cook;
                    $itemCook['price'] = $cook ? $cook['price'] : '0.00';
                    $item->cook = $itemCook;

                    $sauce = $sauces->firstWhere('code', $item->sauce['code']);
                    $itemSauce = $item->sauce;
                    $itemSauce['price'] = $sauce ? $sauce['price'] : '0.00';
                    $item->sauce = $itemSauce;

                    $spice = $spices->firstWhere('code', $item->spices['code']);
                    $itemSpices = $item->spices;
                    $itemSpices['price'] = $spice ? $spice['price'] : '0.00';
                    $item->spices = $itemSpices;
                }
                return response()->json(["message" => "Data found", "data" => $signaturePizzas], 200);
            }
            return response()->json(["message" => "Data not found"], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }
}
