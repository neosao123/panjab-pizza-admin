<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\CrustType;
use Illuminate\Http\Request;
use App\Models\Pizzas;
use App\Models\Softdrinks;
use App\Models\SidesMaster;
use App\Models\SidelineEntries;
use App\Models\Crust;
use App\Models\Cheese;
use App\Models\Specialbases;
use App\Models\Sauce;
use App\Models\Spices;
use App\Models\Cook;
use App\Models\Specialofferlineentries;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class PizzasController extends Controller
{
    // return all  pizzas
    public function list(Request $r)
    {
        try {
            $specialOfferArray = [];
            $cheeses = Cheese::select('code', 'cheese as cheeseName', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
            $crusts = Crust::select('code', 'crust as crustName', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
            $crustTypes = CrustType::select('code', 'crustType', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
            $specialBases = Specialbases::select('code', 'specialbase as specialbaseName', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
            $cooks = Cook::select('code as cookCode', 'cook', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
            $sauces = Sauce::select('code as sauceCode', 'sauce', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
            $spices = Spices::select('code as spicyCode', 'spicy', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();

            $records = Pizzas::with('category')->whereNotNull("pizza_prices")->where("isActive", 1)->orderBy('id', 'DESC')->get();

            if ($records && count($records) > 0) {
                $pizzas = $records;
                foreach ($pizzas as $item) {
                    $path = url("uploads/pizza.jpg");
                    if ($item->pizza_image != "" && $item->pizza_image != null) {
                        $path = url("uploads/pizzas/" . $item->pizza_image);
                    }
                    $item->pizza_image = $path;

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
                return response()->json(["message" => "Data found", "data" => $pizzas], 200);
            }
            return response()->json(["message" => "Data not found"], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }


    // return pizzas by code
    public function show($code)
    {
        try {
            if (!$code) {
                $response = [
                    "message" => "Invalid request"
                ];
                return response()->json($response, 500);
            }

            $pizza = Pizzas::with('category')->whereNotNull("pizza_prices")->where("isActive", 1)->where([
                ['code', $code],
                ['isActive', 1]
            ])->first();

            if ($pizza) {
                $path = url("uploads/pizza.jpg");
                if ($pizza->pizza_image != null && $pizza->pizza_image != "") {
                    $path = url("uploads/pizzas/" . $pizza->pizza_image);
                }

                $pizza->pizza_image = $path;
                $pizza->crusts = Crust::select('code', 'crust as crustName', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
                $pizza->cheeses = Cheese::select('code', 'cheese as cheeseName', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
                $pizza->crustType = CrustType::select('code as crustTypeCode', 'crustType', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
                $pizza->specialBases = Specialbases::select('code', 'specialbase as specialbaseName', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
                $pizza->cooks = Cook::select('code as cookCode', 'cook', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
                $pizza->sauces = Sauce::select('code as sauceCode', 'sauce', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();
                $pizza->spicy = Spices::select('code as spicyCode', 'spicy', 'price')->where('isActive', 1)->orderBy("id", "ASC")->get();

                return response()->json(["message" => "Data found", "data" => $pizza], 200);
            }
            return response()->json(["message" => "Data not found"], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }
}
