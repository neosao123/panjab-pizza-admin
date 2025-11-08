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
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

use function Laravel\Prompts\select;

class CommonController extends Controller
{
    public function __construct(GlobalModel $model, ApiModel $apimodel)
    {
        $this->model = $model;
        $this->apimodel = $apimodel;
    }

    public function cheese()
    {
        try {
            $cheeseArray = [];
            /*$cacheKey = 'cheese';
            if (Cache::has($cacheKey)) {
                $cheese = Cache::get($cacheKey);
            } else {*/
            $cheese = Cheese::where('isActive', 1)->orderBy("id", "DESC")->get();
            //Cache::put($cacheKey, $cheese, 600);
            //}
            if ($cheese && count($cheese) > 0) {
                foreach ($cheese as $item) {
                    $data = ["cheeseCode" => $item->code, "cheeseName" => $item->cheese, "price" => $item->price];
                    array_push($cheeseArray, $data);
                }
                return response()->json(["status" => 200, "message" => "Data found", "data" => $cheeseArray], 200);
            }
            return response()->json(["status" => 300, "message" => "No Data found"], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function crust()
    {
        try {
            $crustArray = [];
            //$cacheKey = 'crust';
            //if (Cache::has($cacheKey)) {
            //$crust = Cache::get($cacheKey);
            //} else {
            $crust = Crust::where('isActive', 1)->orderBy("id", "DESC")->get();
            //Cache::put($cacheKey, $crust, 600);
            //}

            if ($crust && count($crust) > 0) {
                foreach ($crust as $item) {
                    $data = ["crustCode" => $item->code, "crustName" => $item->crust, "price" => $item->price];
                    array_push($crustArray, $data);
                }
                return response()->json(["status" => 200, "message" => "Data found", "data" => $crustArray], 200);
            }
            return response()->json(["status" => 300, "message" => "No Data found"], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function dips()
    {
        try {
            $dipsArray = [];

            //$cacheKey = 'dips';
            ///if (Cache::has($cacheKey)) {
            //$dips = Cache::get($cacheKey);
            //} else {
            $dips = Dips::where('isActive', 1)->orderBy("id", "DESC")->get();
            //Cache::put($cacheKey, $dips, 600);
            //}
            if ($dips && count($dips) > 0) {
                foreach ($dips as $item) {
                    $path = "";
                    if ($item->dipsImage != "" && $item->dipsImage != null) {
                        $path = url("uploads/dips/" . $item->dipsImage);
                    }
                    $data = ["dipsCode" => $item->code,  "ratings" => $item->ratings, "dipsName" => $item->dips, "image" => $path, "price" => $item->price];
                    array_push($dipsArray, $data);
                }
                return response()->json(["status" => 200, "message" => "Data found", "data" => $dipsArray], 200);
            }
            return response()->json(["status" => 300, "message" => "No Data found"], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function softDrinks()
    {
        try {
            $softdrinksArray = [];
            $cacheKey = 'softdrinks';
            //if (Cache::has($cacheKey)) {
            //$softdrinks = Cache::get($cacheKey);
            //} else {
            $softdrinks = Softdrinks::where('isActive', 1)->orderBy("id", "DESC")->get();
            // Cache::put($cacheKey, $softdrinks, 600);
            //}
            if ($softdrinks && count($softdrinks) > 0) {
                foreach ($softdrinks as $item) {

                    $typeDrinks = [];
                    if ($item->code == "SFD_5") {
                        $getTypeDrinks = DB::table("juice")
                            ->select("juice.*")
                            ->where("juice.isActive", 1)
                            ->where("juice.isDelete", 0)
                            ->get();
                        if ($getTypeDrinks && count($getTypeDrinks) > 0) {
                            foreach ($getTypeDrinks as $items) {
                                array_push($typeDrinks, $items->juice);
                            }
                        }
                    } else if ($item->code == "SFD_1") {
                        $typeDrinks = ['Coke'];
                    } else {
                        $getTypeDrinks = DB::table("typedrinks")
                            ->select("typedrinks.*")
                            ->where("typedrinks.isActive", 1)
                            ->where("typedrinks.isDelete", 0)
                            ->get();
                        if ($getTypeDrinks && count($getTypeDrinks) > 0) {
                            foreach ($getTypeDrinks as $items) {
                                array_push($typeDrinks, $items->drinks);
                            }
                        }
                    }

                    $path = "";
                    if ($item->softDrinkImage != "" && $item->softDrinkImage != null) {
                        $path = url("uploads/softdrinks/" . $item->softDrinkImage);
                    }
                    $data = ["softdrinkCode" => $item->code, "ratings" => $item->ratings, "softDrinksName" => $item->softdrinks, "image" => $path, "price" => $item->price, "drinkType" => $typeDrinks, "drinksCount" => $item->drinksCount, "drinksType" => $item->drinksType];
                    array_push($softdrinksArray, $data);
                }
                return response()->json(["status" => 200, "message" => "Data found", "data" => $softdrinksArray], 200);
            }
            return response()->json(["status" => 300, "message" => "No Data found"], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function specialBases()
    {
        try {
            $specialBasesArray = [];
            //$cacheKey = 'specialbases';
            //if (Cache::has($cacheKey)) {
            //$specialbases = Cache::get($cacheKey);
            //} else {
            $specialbases = Specialbases::where('isActive', 1)->orderBy("id", "DESC")->get();
            //Cache::put($cacheKey, $specialbases, 600);
            //}
            if ($specialbases && count($specialbases) > 0) {
                foreach ($specialbases as $item) {
                    $data = ["specialbaseCode" => $item->code, "specialbaseName" => $item->specialbase, "price" => $item->price];
                    array_push($specialBasesArray, $data);
                }
                return response()->json(["status" => 200, "message" => "Data found", "data" => $specialBasesArray], 200);
            }
            return response()->json(["status" => 300, "message" => "No Data found"], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function storeLocations(Request $request)
    {
        try {
            $storeLocationArray = [];
            $userLatitude = $request->lat;
            $userLongitude = $request->lng;
            if (($request->has('lat') && $request->lat != "") && ($request->has('lng') && $request->lng != "")) {


                $storeLocation = Storelocation::with('province')->select(
                    'code',
                    'city',
                    'timezone as timeZone',
                    'storeLocation',
                    'latitude',
                    'longitude',
                    'storeAddress',
                    'tax_province_id',
                    DB::raw("ROUND((6371 * acos(cos(radians($userLatitude))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians($userLongitude))
                        + sin(radians($userLatitude))
                        * sin(radians(latitude)))),2) AS distance")
                )
                    ->where('isActive', 1)
                    ->orderBy('distance', 'ASC')
                    ->get();

                if (count($storeLocation) > 0) {
                    $i = 0;
                    $result = [];
                    foreach ($storeLocation as $item) {

                        $item->isNearestStore = 0;
                        if ($i == 0) {
                            $item->isNearestStore = 1;
                        }
                        $i++;

                        $result[] = [
                            'code' => $item->code,
                            'city' => $item->city,
                            'timeZone' => $item->timeZone,
                            'storeLocation' => $item->storeLocation,
                            'latitude' => $item->latitude,
                            'longitude' => $item->longitude,
                            'storeAddress' => $item->storeAddress,
                            'distance' => $item->distance,
                            'province' => $item->province,
                            'isNearestStore' => $item->isNearestStore,
                        ];
                    }
                    return response()->json(["status" => 200, "message" => "data found", "data" => $result], 200);
                }
                return response()->json(["status" => 200, "message" => "No nearest stores found. Please try with different deilivery address"], 200);
            } else {
                $storeLocation = Storelocation::with('province')->where('isActive', 1)->orderBy("storeLocation", "ASC")->get();

                if ($storeLocation && count($storeLocation) > 0) {
                    foreach ($storeLocation as $item) {
                        $data = [
                            "code" => $item->code,
                            "city" => $item->city,
                            "timeZone" => $item->timezone,
                            "storeLocation" => $item->storeLocation,
                            "storeAddress" => $item->storeAddress,
                            "latitude" => $item->latitude,
                            "longitude" => $item->longitude,
                            "distance" => 0,
                            "isNearestStore" => 0,
                            "province" => $item->province,
                        ];
                        array_push($storeLocationArray, $data);
                    }
                    return response()->json(["status" => 200, "message" => "Data found", "data" => $storeLocationArray], 200);
                }
                return response()->json(["status" => 300, "message" => "No Data found"], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function toppings()
    {
        try {
            $toppingsArray = [];
            $countAsOne = [];
            $countAsTwo = [];
            $freeToppings = [];
            $toppingsone = Toppings::where('isActive', 1)
                //->where("toppings.countAs", 1)
                ->where("toppings.topping_type", 'regular')
                ->where("toppings.isPaid", 1)
                ->orderBy("id", "ASC")
                ->get();
            $toppingstwo = Toppings::where('isActive', 1)
                //->where("toppings.countAs", 2)
                ->where("toppings.topping_type", 'non-regular')
                ->where("toppings.isPaid", 1)
                ->orderBy("id", "ASC")
                ->get();
            $freeTopping = Toppings::where('isActive', 1)
                ->where("toppings.isPaid", 0)
                ->orderBy("id", "ASC")
                ->get();
            if ($toppingsone && count($toppingsone) > 0) {
                foreach ($toppingsone as $item) {
                    $path = "";
                    if ($item->toppingsImage != "" && $item->toppingsImage != null) {
                        $path = url("uploads/toppings/" . $item->toppingsImage);
                    }
                    $data = ["toppingsCode" => $item->code, "toppingsName" => $item->toppingsName, "image" => $path, "countAs" => $item->countAs, "price" => $item->price, "isPaid" => $item->isPaid];
                    array_push($countAsOne, $data);
                }
            }
            if ($toppingstwo && count($toppingstwo) > 0) {
                foreach ($toppingstwo as $item) {
                    $path = "";
                    if ($item->toppingsImage != "" && $item->toppingsImage != null) {
                        $path = url("uploads/toppings/" . $item->toppingsImage);
                    }
                    $data = ["toppingsCode" => $item->code, "toppingsName" => $item->toppingsName, "image" => $path, "countAs" => $item->countAs, "price" => $item->price, "isPaid" => $item->isPaid];
                    array_push($countAsTwo, $data);
                }
            }

            if ($freeTopping && count($freeTopping) > 0) {
                foreach ($freeTopping as $item) {
                    $path = "";
                    if ($item->toppingsImage != "" && $item->toppingsImage != null) {
                        $path = url("uploads/toppings/" . $item->toppingsImage);
                    }
                    $data = ["toppingsCode" => $item->code, "toppingsName" => $item->toppingsName, "image" => $path, "countAs" => $item->countAs, "price" => $item->price, "isPaid" => $item->isPaid];
                    array_push($freeToppings, $data);
                }
            }
            $toppingsArray = ["countAsOne" => $countAsOne, "countAsTwo" => $countAsTwo, "freeToppings" => $freeToppings];
            return response()->json(["status" => 200, "message" => "Data found", "data" => ["toppings" => $toppingsArray]], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function sides(Request $r)
    {
        try {
            $sidesArray = [];
            $validator = Validator::make($r->all(), [
                'type' => 'nullable'
            ]);
            if ($validator->fails()) {
                return response()->json(["status" => 500, "message" => $validator->errors()->first()], 200);
            }
            $sideQuery = SidesMaster::where('isActive', 1);
            if ($r->type != "") {
                $sideQuery->where("type", $r->type);
            }
            $sideQuery->orderBy("id", "DESC");
            $sides = $sideQuery->get();
            if ($sides && count($sides) > 0) {
                foreach ($sides as $item) {
                    $path = url("uploads/sample_sides.png");
                    if ($item->image != "" && $item->image != null) {
                        $path = url("uploads/sides/" . $item->image);
                    }
                    $combinationArray = [];
                    $sidelineEntries = SidelineEntries::where("isActive", 1)->orderBy("id", "DESC")->where("sidemasterCode", $item->code)->get();
                    if ($sidelineEntries && count($sidelineEntries) > 0) {
                        foreach ($sidelineEntries as $items) {
                            $linedata = ["lineCode" => $items->code, "size" => $items->size, "price" => $items->price];
                            array_push($combinationArray, $linedata);
                        }
                    }

                    $sideToppingsArray = [];
                    if ($item->hasToppings == 1 && $item->nooftoppings > 0) {
                        $sideToppings = DB::table('sides_toppings')->where('isActive', 1)->get();
                        foreach ($sideToppings as $data) {
                            $toppings = ['code' => $data->code, 'toppingsName' => $data->toppingsName];
                            array_push($sideToppingsArray, $toppings);
                        }
                    }

                    $data = ["sideCode" => $item->code, "sideName" => $item->sidename, "ratings" => $item->ratings, "image" => $path, "type" => $item->type, 'hasToppings' => $item->hasToppings, 'nooftoppings' => $item->nooftoppings, "combination" => $combinationArray, "sidesToppings" => $sideToppingsArray];
                    array_push($sidesArray, $data);
                }
                return response()->json(["status" => 200, "message" => "Data found", "data" => $sidesArray], 200);
            }
            return response()->json(["status" => 300, "message" => "No Data found"], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function typeWiseSearchableSides(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'search' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(["status" => 500, "message" => $validator->errors()->first()], 200);
            }

            // Initialize the query
            $sideQuery = SidesMaster::where('isActive', 1);

            // Apply textual search if provided
            if (!empty($r->search)) {
                $sideQuery->where(function ($query) use ($r) {
                    $query->where('sidename', 'like', '%' . $r->search . '%');
                });
            }

            // Order and get results
            $sides = $sideQuery->orderBy("type", "ASC")->orderBy("id", "DESC")->get();

            if ($sides->isEmpty()) {
                return response()->json(["status" => 300, "message" => "No Data found"], 200);
            }

            // Group data by type
            $groupedData = [];
            foreach ($sides->groupBy('type') as $type => $sidesByType) {
                $sidesArray = [];
                foreach ($sidesByType as $item) {
                    $path = url("uploads/sample_sides.png");
                    if (!empty($item->image)) {
                        $path = url("uploads/sides/" . $item->image);
                    }

                    // Fetch sideline combinations
                    $combinationArray = [];
                    $sidelineEntries = SidelineEntries::where("isActive", 1)
                        ->where("sidemasterCode", $item->code)
                        ->orderBy("id", "DESC")
                        ->get();
                    foreach ($sidelineEntries as $entry) {
                        $combinationArray[] = [
                            "lineCode" => $entry->code,
                            "size" => $entry->size,
                            "price" => $entry->price,
                        ];
                    }

                    // Fetch side toppings
                    $sideToppingsArray = [];
                    if ($item->hasToppings == 1 && $item->nooftoppings > 0) {
                        $sideToppings = DB::table('sides_toppings')
                            ->where('isActive', 1)
                            ->get();
                        foreach ($sideToppings as $topping) {
                            $sideToppingsArray[] = [
                                'code' => $topping->code,
                                'toppingsName' => $topping->toppingsName,
                            ];
                        }
                    }

                    $sidesArray[] = [
                        "sideCode" => $item->code,
                        "sideName" => $item->sidename,
                        "ratings" => $item->ratings,
                        "image" => $path,
                        "type" => $item->type,
                        'hasToppings' => $item->hasToppings,
                        'nooftoppings' => $item->nooftoppings,
                        "combination" => $combinationArray,
                        "sidesToppings" => $sideToppingsArray,
                    ];
                }

                $groupedData[] = [
                    "type" => $type,
                    "sides" => $sidesArray,
                    "count" => count($sidesArray)
                ];
            }
            // Sort the grouped data by count in descending order
            usort($groupedData, function ($a, $b) {
                return $b['count'] <=> $a['count'];
            });

            return response()->json(["status" => 200, "message" => "Data found", "data" => $groupedData], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function allIngredients()
    {
        try {
            $cheeseArray = [];
            $crustArray = [];
            $crustTypeArray = [];
            $dipsArray = [];
            $softdrinksArray = [];
            $specialbasesArray = [];
            $toppingsArray = [];
            $countAsOne = [];
            $countAsTwo = [];
            $freeToppings = [];
            $sauce = [];
            $spices = [];
            $cooks = [];


            $sides = SidesMaster::where('isActive', 1)->orderBy("id", "DESC")->get();
            $cook = Cook::where('isActive', 1)->orderBy("id", "ASC")->get();
            $spicy = Spices::where('isActive', 1)->orderBy("id", "ASC")->get();
            $sauces = Sauce::where('isActive', 1)->orderBy("id", "ASC")->get();
            $cheese = Cheese::where('isActive', 1)->orderBy("id", "ASC")->get();
            $crust = Crust::where('isActive', 1)->orderBy("id", "ASC")->get();
            $crustType = CrustType::where('isActive', 1)->orderBy("id", "ASC")->get();
            $specialbases = Specialbases::where('isActive', 1)->orderBy("id", "ASC")->get();
            $dips = Dips::where('isActive', 1)->orderBy("id", "DESC")->get();
            $softdrinks = Softdrinks::where('isActive', 1)->orderBy("id", "DESC")->get();
            $pizzaPrices = DB::table('pizza_prices')->get();
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
            $freeTopping = Toppings::where('isActive', 1)
                ->where("toppings.isPaid", 0)
                ->orderBy("id", "ASC")
                ->get();

            if ($cook && count($cook) > 0) {
                foreach ($cook as $item) {
                    $data = ["cookCode" => $item->code, "cook" => $item->cook, "isActive" => $item->isActive, "price" => $item->price];
                    array_push($cooks, $data);
                }
            }

            if ($sauces && count($sauces) > 0) {
                foreach ($sauces as $item) {
                    $data = ["sauceCode" => $item->code, "sauce" => $item->sauce, "price" => $item->price, "isActive" => $item->isActive];
                    array_push($sauce, $data);
                }
            }

            if ($spicy && count($spicy) > 0) {
                foreach ($spicy as $item) {
                    $data = ["spicyCode" => $item->code, "spicy" => $item->spicy, "price" => $item->price, "isActive" => $item->isActive];
                    array_push($spices, $data);
                }
            }

            if ($cheese && count($cheese) > 0) {
                foreach ($cheese as $item) {
                    $data = ["cheeseCode" => $item->code, "price" => $item->price, "cheeseName" => $item->cheese];
                    array_push($cheeseArray, $data);
                }
            }

            if ($crust && count($crust) > 0) {
                foreach ($crust as $item) {
                    $data = ["crustCode" => $item->code, "price" => $item->price, "crustName" => $item->crust];
                    array_push($crustArray, $data);
                }
            }

            if ($crustType && count($crustType) > 0) {
                foreach ($crustType as $item) {
                    $data = ["crustTypeCode" => $item->code, "price" => $item->price, "crustType" => $item->crustType];
                    array_push($crustTypeArray, $data);
                }
            }

            if ($dips && count($dips) > 0) {
                foreach ($dips as $item) {
                    $path = "";
                    if ($item->dipsImage != "" && $item->dipsImage != null) {
                        $path = url("uploads/dips/" . $item->dipsImage);
                    }
                    $data = ["dipsCode" => $item->code, "dipsName" => $item->dips, "image" => $path, "price" => $item->price, "ratings" => $item->ratings];
                    array_push($dipsArray, $data);
                }
            }

            if ($softdrinks && count($softdrinks) > 0) {
                foreach ($softdrinks as $item) {
                    $typeDrinks = [];
                    if ($item->code == "SFD_5") {
                        $getTypeDrinks = DB::table("juice")
                            ->select("juice.*")
                            ->where("juice.isActive", 1)
                            ->where("juice.isDelete", 0)
                            ->get();
                        if ($getTypeDrinks && count($getTypeDrinks) > 0) {
                            foreach ($getTypeDrinks as $items) {
                                array_push($typeDrinks, $items->juice);
                            }
                        }
                    } else {
                        $getTypeDrinks = DB::table("typedrinks")
                            ->select("typedrinks.*")
                            ->where("typedrinks.isActive", 1)
                            ->where("typedrinks.isDelete", 0)
                            ->get();
                        if ($getTypeDrinks && count($getTypeDrinks) > 0) {
                            foreach ($getTypeDrinks as $items) {
                                array_push($typeDrinks, $items->drinks);
                            }
                        }
                    }
                    $path = "";
                    if ($item->softDrinkImage != "" && $item->softDrinkImage != null) {
                        $path = url("uploads/softdrinks/" . $item->softDrinkImage);
                    }
                    $data = ["softdrinkCode" => $item->code, "ratings" => $item->ratings, "softDrinksName" => $item->softdrinks, "image" => $path, "price" => $item->price, "drinkType" => $typeDrinks];
                    array_push($softdrinksArray, $data);
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
                    $path = "";
                    if ($item->toppingsImage != "" && $item->toppingsImage != null) {
                        $path = url("uploads/toppings/" . $item->toppingsImage);
                    }
                    $data = ["toppingsCode" => $item->code, "toppingsName" => $item->toppingsName, "image" => $path, "countAs" => $item->countAs, "price" => $item->price, "isPaid" => $item->isPaid];
                    array_push($countAsOne, $data);
                }
            }

            if ($toppingstwo && count($toppingstwo) > 0) {
                foreach ($toppingstwo as $item) {
                    $path = "";
                    if ($item->toppingsImage != "" && $item->toppingsImage != null) {
                        $path = url("uploads/toppings/" . $item->toppingsImage);
                    }
                    $data = ["toppingsCode" => $item->code, "toppingsName" => $item->toppingsName, "image" => $path, "countAs" => $item->countAs, "price" => $item->price, "isPaid" => $item->isPaid];
                    array_push($countAsTwo, $data);
                }
            }

            if ($freeTopping && count($freeTopping) > 0) {
                foreach ($freeTopping as $item) {
                    $path = "";
                    if ($item->toppingsImage != "" && $item->toppingsImage != null) {
                        $path = url("uploads/toppings/" . $item->toppingsImage);
                    }
                    $data = ["toppingsCode" => $item->code, "toppingsName" => $item->toppingsName, "image" => $path, "countAs" => $item->countAs, "price" => $item->price, "isPaid" => $item->isPaid];
                    array_push($freeToppings, $data);
                }
            }
            $toppingsArray = ["countAsOne" => $countAsOne, "countAsTwo" => $countAsTwo, "freeToppings" => $freeToppings];


            $sidesArray = [];
            if ($sides && count($sides) > 0) {
                foreach ($sides as $item) {
                    $path = url("uploads/sample_sides.png");
                    if ($item->image != "" && $item->image != null) {
                        $path = url("uploads/sides/" . $item->image);
                    }
                    $combinationArray = [];
                    $sidelineEntries = SidelineEntries::where("isActive", 1)->orderBy("id", "DESC")->where("sidemasterCode", $item->code)->get();
                    if ($sidelineEntries && count($sidelineEntries) > 0) {
                        foreach ($sidelineEntries as $items) {
                            $linedata = ["lineCode" => $items->code, "size" => $items->size, "price" => $items->price];
                            array_push($combinationArray, $linedata);
                        }
                    }

                    $sideToppingsArray = [];
                    if ($item->hasToppings == 1 && $item->nooftoppings > 0) {
                        $sideToppings = DB::table('sides_toppings')->where('isActive', 1)->get();
                        foreach ($sideToppings as $data) {
                            $toppings = ['code' => $data->code, 'toppingsName' => $data->toppingsName];
                            array_push($sideToppingsArray, $toppings);
                        }
                    }

                    $data = ["sideCode" => $item->code, "sideName" => $item->sidename, "rating" => $item->ratings, "image" => $path, "type" => $item->type, 'hasToppings' => $item->hasToppings, 'nooftoppings' => $item->nooftoppings, "combination" => $combinationArray, "sidesToppings" => $sideToppingsArray];
                    array_push($sidesArray, $data);
                }
            }

            return response()->json([
                "status" => 200,
                "message" => "Data found",
                "data" => [
                    "sizesAndPrices" => $pizzaPrices,
                    "cheese" => $cheeseArray,
                    "crust" => $crustArray,
                    "crustType" => $crustTypeArray,
                    "spices" => $spices,
                    "sauce" => $sauce,
                    "cook" => $cooks,
                    "specialbases" => $specialbasesArray,
                    "toppings" => $toppingsArray,
                    "dips" => $dipsArray,
                    "softdrinks" => $softdrinksArray,
                    "sides" => $sidesArray
                ]
            ], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function settings(Request $r)
    {
        try {
            $settingArray = [];
            $setting = Setting::where('isActive', 1)->orderBy("id", "DESC")->get();
            if ($setting && count($setting) > 0) {
                foreach ($setting as $item) {
                    $data = ["settingCode" => $item->code, "settingName" => $item->settingName, "settingValue" => $item->settingValue, "type" => $item->type];
                    array_push($settingArray, $data);
                }
                return response()->json(["status" => 200, "message" => "Data found", "data" => $settingArray], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function check_zipcode_deliverable(Request $r)
    {
        try {
            $input = $r->all();
            $rules = ['zipcode' => 'required|regex:/^[ABCEGHJKLMNPRSTVXY]\d[A-Z]\d[A-Z]\d$/i'];
            $messages = [
                'zipcode.required' => 'Postal Code is required',
                'zipcode.regex' => 'Enter valid postal code.',
            ];
            $validator = Validator::make($input, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ], 500);
            }
            $result = Zipcode::where('isActive', 1)->where('zipcode', $r->zipcode)->first();
            if (!empty($result)) {
                $store = Storelocation::with('province')->where('code', $result->storeCode)->first();
                if ($store) {
                    return response()->json(["status" => 200, "message" => "data found", 'deliverable' => true, 'taxRates' => $store->province], 200);
                }
            }
            return response()->json(["status" => 200, "message" => "data not found", 'deliverable' => false], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function zipcode_deliverable_list(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'search' => 'nullable',
            ]);
            if ($validator->fails()) {

                return response()->json([
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ], 500);
            }
            $postalCode = [];
            $result = Zipcode::select("zipcode.*")->where('isActive', 1);
            if ($r->has('search') && trim($r->search) != "") {
                $result->where('zipcode.zipcode', 'like', "$r->search%"); // Developer: ShreyasM, Working: 23-12-2023
            }
            $getQuery = $result->limit(10)->get(); // Developer: ShreyasM, Working: 23-12-2023
            if ($getQuery && count($getQuery) > 0) {
                foreach ($getQuery as $items) {
                    $data = ["code" => $items->code, "zipcode" => $items->zipcode];
                    array_push($postalCode, $data);
                }
                return response()->json(["status" => 200, "message" => "data found", "data" => $postalCode], 200);
            }
            return response()->json(["status" => 200, "message" => "data not found"], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function truncateOrders(Request $r)
    {
        try {
            DB::table('ordermaster')->truncate();
            DB::table('orderlineentries')->truncate();
            return response()->json(["status" => 200, "message" => "Data Cleared"], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    //  Developer - Shreyas Mahamuni
    //  Working Date - 22-11-2023
    //  This API for get pizza price
    public function pizzaSizesAndPrices()
    {
        try {
            $pizzaPrices = DB::table('pizza_prices')->get();
            if ($pizzaPrices) {
                return response()->json(["status" => 200, "message" => "Data found", "data" => $pizzaPrices], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function sendContactUsEmail(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'firstName' => 'required|min:3|max:50|regex:/^[a-zA-Z\s]+$/',
                'lastName' => 'required|min:3|max:50|regex:/^[a-zA-Z\s]+$/',
                'email' => [
                    'required',
                    'email',
                ],
                'mobileNumber' => [
                    'required',
                    'digits:10',
                    'numeric',
                ],
                'message' => [
                    'required',
                    'min:3',
                    'max:800'
                ]
            ], [
                'firstName.required' => 'The first name is required.',
                'firstName.min' => 'The first name must be at least 3 characters.',
                'firstName.max' => 'The first name may not be greater than 50 characters.',
                'firstName.regex' => 'The first name may only contain letters and spaces.',
                'lastName.required' => 'The last name is required.',
                'lastName.min' => 'The last name must be at least 3 characters.',
                'lastName.max' => 'The last name may not be greater than 50 characters.',
                'lastName.regex' => 'The last name may only contain letters and spaces.',
                'email.required' => 'The email is required.',
                'email.email' => 'Please provide a valid email address.',
                'mobileNumber.required' => 'The mobile number is required.',
                'mobileNumber.digits' => 'The mobile number must be exactly 10 digits.',
                'mobileNumber.numeric' => 'The mobile number must be numeric.',
                'message.required' => 'The message is required.',
                'message.min' => 'The message must be at least 3 characters.',
                'message.max' => 'The message must be at least 800 characters.',
            ]);
            if ($validator->fails()) {

                return response()->json([
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ], 500);
            }
            // mrsinghpizza@hotmail.com
            Mail::to('testing.neosaoservices@gmail.com')
                ->send(new ContactUsEmail($input));

            return response()->json(["status" => 200, "message" => "Email sent successfully"], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    // Dt. 26-Nov-24
    public function nearestStoreByLatLng(Request $request)
    {
        try {
            if (($request->has('lat') && $request->lat != "") && ($request->has('lng') && $request->lng != "")) {
                $userLatitude = $request->lat;
                $userLongitude = $request->lng;
                $nearestStores = DB::table('storelocation')
                    ->select(
                        'code',
                        'storeLocation',
                        'latitude',
                        'longitude',
                        'storeAddress',
                        'city',
                        'timezone',
                        'weekdays_start_time',
                        'weekdays_end_time',
                        'weekend_start_time',
                        'weekend_end_time',
                        DB::raw("ROUND((6371 * acos(cos(radians($userLatitude))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians($userLongitude))
                    + sin(radians($userLatitude))
                    * sin(radians(latitude)))),2) AS distance")
                    )
                    ->where('isActive', 1)
                    ->orderBy('distance', 'ASC')
                    ->get();
                if (count($nearestStores) > 0) {
                    $i = 0;
                    //if ($nearestStores[0]->distance > 50) {
                    //    return response()->json(["status" => 300, "message" => "Your location is too far away and order cannot be delivered, please try with different address"], 200);
                    //}
                    $result = [];
                    foreach ($nearestStores as $store) {
                        //if ($store->distance <= 50) {
                        $store->isNearestStore = 0;
                        if ($i == 0) {
                            $store->isNearestStore = 1;
                        }
                        $i++;

                        // Add logic to determine the operating hours based on timezone and day of the week
                        $currentDateTime = Carbon::now($store->timezone); // Get current time in store's timezone
                        $dayOfWeek = $currentDateTime->format('l'); // Get the current day (e.g., Monday, Tuesday)
                        $store->dayOfWeek = $dayOfWeek;
                        if (in_array($dayOfWeek, ['Monday', 'Tuesday', 'Wednesday', 'Thursday'])) {
                            $store->start_time = Carbon::createFromFormat('H:i:s', $store->weekdays_start_time)->format('h:i A');
                            $store->end_time = Carbon::createFromFormat('H:i:s', $store->weekdays_end_time)->format('h:i A');
                        } else {
                            $store->start_time = Carbon::createFromFormat('H:i:s', $store->weekend_start_time)->format('h:i A');
                            $store->end_time = Carbon::createFromFormat('H:i:s',   $store->weekend_end_time)->format('h:i A');
                        }
                        unset($store->weekdays_start_time);
                        unset($store->weekdays_end_time);
                        unset($store->weekend_start_time);
                        unset($store->weekend_end_time);

                        $result[] = $store;
                        //}
                    }
                    return response()->json(["status" => 200, "message" => "data found", "data" => $result], 200);
                }
                return response()->json(["status" => 200, "message" => "No nearest stores found. Please try with different deilivery address"], 200);
            } else {
                $allStores = DB::table('storelocation')
                    ->select(
                        'code',
                        'storeLocation',
                        'latitude',
                        'longitude',
                        'storeAddress',
                        'city',
                        'timezone',
                        'weekdays_start_time',
                        'weekdays_end_time',
                        'weekend_start_time',
                        'weekend_end_time'
                    )
                    ->where('isActive', 1)
                    ->orderBy('storeLocation', 'ASC')
                    ->get();
                if (count($allStores) > 0) {
                    $result = [];
                    foreach ($allStores as $store) {
                        $store->distance = 0;
                        $store->isNearestStore = 0;

                        // Add logic to determine the operating hours based on timezone and day of the week
                        $currentDateTime = Carbon::now($store->timezone); // Get current time in store's timezone
                        $dayOfWeek = $currentDateTime->format('l'); // Get the current day (e.g., Monday, Tuesday)
                        $store->dayOfWeek = $dayOfWeek;
                        if (in_array($dayOfWeek, ['Monday', 'Tuesday', 'Wednesday', 'Thursday'])) {
                            $store->start_time = Carbon::createFromFormat('H:i:s', $store->weekdays_start_time)->format('h:i A');
                            $store->end_time = Carbon::createFromFormat('H:i:s', $store->weekdays_end_time)->format('h:i A');
                        } else {
                            $store->start_time = Carbon::createFromFormat('H:i:s', $store->weekend_start_time)->format('h:i A');
                            $store->end_time = Carbon::createFromFormat('H:i:s', $store->weekend_end_time)->format('h:i A');
                        }
                        unset($store->weekdays_start_time);
                        unset($store->weekdays_end_time);
                        unset($store->weekend_start_time);
                        unset($store->weekend_end_time);

                        $result[] = $store;
                    }
                    return response()->json(["status" => 200, "message" => "data found", "data" => $result], 200);
                }
                return response()->json(["status" => 200, "message" => "No stores found"], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function dynamicSliderApp()
    {
        try {
            $sliderArr = [];

            // Fetch all slider data at once
            $sliderData = DynamicSlider::all();

            // If there's any slider data, process it
            if ($sliderData->isNotEmpty()) {
                // Fetch all line entries in one query
                $sliderCodes = $sliderData->pluck('code');
                $lineentriesData = DynamicSliderLineentries::whereIn('slider_code', $sliderCodes)
                    ->select('slider_code', 'store_address')
                    ->get()
                    ->groupBy('slider_code');

                // Process slider data
                foreach ($sliderData as $data) {
                    $lineentries = $lineentriesData->get($data->code, collect())->pluck('store_address');
                    $sliderArr[] = [
                        "code" => $data->code,
                        "subTitle" => $data->subTitle ?? "100% Vegetarian",
                        "background_image" => url('uploads/slider-background/' . $data->background_image),
                        "background_image_md" => $data->background_image_md ? url('uploads/slider-background/' . $data->background_image_md) :  url('uploads/slider-background/' . $data->background_image),
                        "background_image_sm" => $data->background_image_sm ?  url('uploads/slider-background/' . $data->background_image_sm) : url('uploads/slider-background/' . $data->background_image),
                        "title" => $data->title,
                        "lineentries" => $lineentries,
                        "is_static" => "false",
                    ];
                }
            }

            // // Add static slider data only once, outside the loop
            // $staticEntry = [
            //     "code" => 'static',
            //     "subTitle" => "100% Vegetarian",
            //     "background_image" => url('public/images/slider-default.jpg'),
            //     "background_image_md" => url('public/images/slider-default.jpg'),
            //     "background_image_sm" => url('public/images/slider-default.jpg'),
            //     "title" => "create your own",
            //     "lineentries" => [],
            //     "is_static" => "true",
            // ];

            // Add static entry twice with different titles
            //   $sliderArr[] = $staticEntry;
            //   $sliderArr[] = array_merge($staticEntry, ["title" => "special pizza's and combo's"]);

            return response()->json(["status" => 200, 'message' => 'success', 'data' => $sliderArr], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function dynamicSliderWeb()
    {
        try {
            $sliderArr = [];
            $sliderData = DynamicSlider::get();
            if (count($sliderData) > 0) {
                foreach ($sliderData as $data) {
                    $lineentries = DynamicSliderLineentries::where('slider_code', $data->code)->get();
                    $sliderArr[] = [
                        "code" => $data->code,
                        "title" => $data->title,
                        "background_image" => url('uploads/slider-background/' . $data->background_image),
                        "background_image_md" => $data->background_image_md ? url('uploads/slider-background/' . $data->background_image_md) :  url('uploads/slider-background/' . $data->background_image),
                        "background_image_sm" => $data->background_image_sm ?  url('uploads/slider-background/' . $data->background_image_sm) : url('uploads/slider-background/' . $data->background_image),
                        "lineentries" => $lineentries,
                        "btnName" => "",
                        "url" => "",
                        "is_static" => "false",
                    ];
                }
            }
            /*
      $sliderArr[] = [
        "is_static" => "true",
        "code" => "static",
        "subTitle" => "100% Vegetarian",
        "background_image" => url('public/images/slider-default.jpg'),
        "background_image_md" => url('public/images/slider-default.jpg'),
        "background_image_sm" => url('public/images/slider-default.jpg'),
        "title" => "Create Your Own",
        "btnName" => "Create",
        "url" => "/create-your-own",
        "lineentries" => []
      ];

      $sliderArr[] = [
        "is_static" => "true",
        "code" => "static",
        "subTitle" => "100% Vegetarian",
        "title" => "special pizza's and combo's",
        "background_image" => url('public/images/slider-default.jpg'),
        "background_image_md" => url('public/images/slider-default.jpg'),
        "background_image_sm" => url('public/images/slider-default.jpg'),
        "btnName" => "View",
        "url" => "/special-list",
        "lineentries" => []
      ];
      */
            return response()->json(['message' => 'success', "data" => $sliderArr], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function order_list(Request $r, $code)
    {
        try {
            $yesterday = \Carbon\Carbon::yesterday();
            $orders = DB::table("ordermaster")
                ->where('storeLocation', $code)
                ->where('orderStatus', 'pending')
                ->whereDate('orderDate', $yesterday) // Filter orders with order_date as yesterday
                ->pluck('code');
            if (!empty($orders)) {
                return response()->json([
                    "status" => 200,
                    "message" => "Orders found",
                    "data" => $orders
                ], 200);
            } else {
                return response()->json(['status' => 300, 'message' => 'Data not found.'], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function pizzaToppingsConfig($code, $section)
    {

        if ($section == "other") {
            $pizza = Pizzas::select('topping_as_2', 'topping_as_1')->where('code', $code)->first();
        }

        if ($section == "signature") {
            $pizza = SignaturePizza::select('topping_as_2', 'topping_as_1')->where('code', $code)->first();
        }

        if ($pizza) {
            $oneToppings = Toppings::select("code as toppingsCode", "toppingsName", "countAs", "price", "isPaid")
                ->where('isActive', 1)
                ->where("toppings.topping_type", 'regular')
                ->where("toppings.isPaid", 1)
                ->orderBy("id", "ASC")
                ->get();
            $twoToppings = Toppings::select("code as toppingsCode", "toppingsName", "countAs", "price", "isPaid")
                ->where('isActive', 1)
                ->where("toppings.topping_type", 'non-regular')
                ->where("toppings.isPaid", 1)
                ->orderBy("id", "ASC")
                ->get();
            $freeToppings = Toppings::select("code as toppingsCode", "toppingsName", "countAs", "price", "isPaid")
                ->where('isActive', 1)
                ->where("toppings.isPaid", 0)
                ->orderBy("id", "ASC")
                ->get();

            $pizzaTwoToppings = $pizza ? $pizza->topping_as_2 : [];
            $pizzaOneToppings = $pizza ? $pizza->topping_as_1 : [];


            if (count($oneToppings) > 0) {

                $fToppings = [];
                foreach ($pizzaOneToppings as $t) {
                    array_push($fToppings, $t['code']);
                }

                foreach ($oneToppings as $topping) {
                    if (in_array($topping->toppingsCode, $fToppings)) {
                        $topping->price = number_format(0, 2);
                        $topping->isPaid = 0;
                    }
                }
            }

            if (count($twoToppings) > 0) {
                $fToppings = [];
                foreach ($pizzaTwoToppings as $t) {
                    array_push($fToppings, $t['code']);
                }
                foreach ($twoToppings as $topping) {
                    if (in_array($topping->toppingsCode, $fToppings)) {
                        $topping->price = number_format(0, 2);
                        $topping->isPaid = 0;
                    }
                }
            }

            $toppingsArray = ["countAsTwo" => $twoToppings->sortBy('price')->values(), "countAsOne" => $oneToppings->sortBy('price')->values(), "freeToppings" => $freeToppings];
            return response()->json(["status" => 200, "message" => "Data found", "data" => ["toppings" => $toppingsArray]], 200);
        } else {
            return response()->json(["status" => 300, "message" => "No Data found"], 400);
        }
    }

    private function groupCommanData()
    {
        $ttl = 1;
        $commanArray = Cache::remember('ramdom-order-products', $ttl, function () {
            $dips = Dips::where("isActive", 1)->orderBy('id', 'DESC')->limit(2)->inRandomOrder()->get();
            $drinks = Softdrinks::where("isActive", 1)->orderBy('id', 'DESC')->limit(2)->inRandomOrder()->get();
            $sides = SidesMaster::where("isActive", 1)->orderBy('id', 'DESC')->limit(2)->inRandomOrder()->get();
            $other = Pizzas::where("isActive", 1)->orderBy('id', 'DESC')->limit(2)->inRandomOrder()->get();
            $signature = SignaturePizza::where("isActive", 1)->orderBy('id', 'DESC')->limit(2)->inRandomOrder()->get();

            $commanArray = [];

            foreach ($dips as $item) {
                $path = url("uploads/sample_dips.png");
                if ($item->dipsImage != "" && $item->dipsImage != null) {
                    $path = url("uploads/dips/" . $item->dipsImage);
                }
                $commanArray[] = [
                    "code" => $item->code,
                    "name" => $item->dips,
                    "image" => $path,
                    "ratings" => $item->ratings,
                    "type" => ucwords("new"),
                    "productType" => "dips"
                ];
            }
            foreach ($drinks as $item) {
                $path = url("uploads/sample_drinks.png");
                if ($item->softDrinkImage != "" && $item->softDrinkImage != null) {
                    $path = url("uploads/softdrinks/" . $item->softDrinkImage);
                }
                $commanArray[] = [
                    "code" => $item->code,
                    "name" => $item->softdrinks,
                    "image" => $path,
                    "ratings" => $item->ratings,
                    "type" => ucwords("new"),
                    "productType" => "drinks"
                ];
            }
            foreach ($sides as $item) {
                $path = url("uploads/sample_sides.png");
                if ($item->image != "" && $item->image != null) {
                    $path = url("uploads/sides/" . $item->image);
                }
                $commanArray[] = [
                    "code" => $item->code,
                    "name" => $item->sidename,
                    "image" => $path,
                    "ratings" => $item->ratings,
                    "type" => ucwords("new"),
                    "productType" => "sides"
                ];
            }
            foreach ($other as $item) {
                $path = url("uploads/pizza.jpg");
                if ($item->pizza_image != "" && $item->pizza_image != null) {
                    $path = url("uploads/pizzas/" . $item->pizza_image);
                }
                $commanArray[] = [
                    "code" => $item->code,
                    "name" => $item->pizza_name,
                    "image" => $path,
                    "ratings" => $item->ratings,
                    "type" => ucwords("new"),
                    "productType" => "other"
                ];
            }
            foreach ($signature as $item) {
                $path = url("uploads/pizza.jpg");
                if ($item->pizza_image != "" && $item->pizza_image != null) {
                    $path = url("uploads/signature-pizza/" . $item->pizza_image);
                }
                $item->pizza_image = $path;
                $commanArray[] = [
                    "code" => $item->code,
                    "name" => $item->pizza_name,
                    "image" => $path,
                    "ratings" => $item->ratings,
                    "type" => ucwords("new"),
                    "productType" => "signature"
                ];
            }
            return $commanArray;
        });


        return $commanArray;
    }

    public function homePagePizzas(Request $r)
    {
        try {

            $other = $signature = $special = [];
            $records  = Specialoffer::select("specialoffer.*")
                ->whereNotNull("pizza_prices")
                ->where("isActive", 1)
                ->where("showOnClient", 1)
                ->where(function ($query) {
                    $query->whereNull("limited_offer")
                        ->orWhere("limited_offer", 0);
                })
                ->orderBy('id', 'DESC')->limit(8)->get();
            if ($records && count($records) > 0) {
                foreach ($records as $item) {
                    $path = url("uploads/pizza.jpg");
                    if ($item->specialofferphoto != "" && $item->specialofferphoto != null) {
                        $path = url("uploads/specialoffer/" . $item->specialofferphoto);
                    }
                    $special[] = [
                        'code' => $item->code,
                        'pizzaName' => $item->name,
                        'pizzaSubtitle' => $item->subtitle,
                        'pizzaImage' => $path,
                        'ratings' => $item->ratings
                    ];
                }
            }

            $records = SignaturePizza::where("isActive", 1)->orderBy('id', 'DESC')->limit(8)->get();
            if ($records && count($records) > 0) {
                foreach ($records as $item) {
                    $path = url("uploads/pizza.jpg");
                    if ($item->pizza_image != "" && $item->pizza_image != null) {
                        $path = url("uploads/signature-pizza/" . $item->pizza_image);
                    }
                    $signature[] = [
                        'code' => $item->code,
                        'pizzaName' => $item->pizza_name,
                        'pizzaSubtitle' => $item->pizza_subtitle,
                        'pizzaImage' => $path,
                        'ratings' => $item->ratings
                    ];
                }
            }

            $records = Pizzas::where("isActive", 1)->orderBy('id', 'DESC')->limit(8)->get();
            if ($records && count($records) > 0) {
                foreach ($records as $item) {
                    $path = url("uploads/pizza.jpg");
                    if ($item->pizza_image != "" && $item->pizza_image != null) {
                        $path = url("uploads/pizzas/" . $item->pizza_image);
                    }
                    $other[] = [
                        'code' => $item->code,
                        'pizzaName' => $item->pizza_name,
                        'pizzaSubtitle' => $item->pizza_subtitle,
                        'pizzaImage' => $path,
                        'ratings' => $item->ratings
                    ];
                }
            }

            $popularItems = $this->groupCommanData();

            if ($r->has('combine') && $r->combine == 1) {
                $allPizzas = array_merge($other, $signature, $special);
                $data['otherPizzas'] = $allPizzas;
                $data['popularItems'] = $popularItems;
            } else {
                $data['otherPizzas'] = $other;
                $data['signaturePizzas'] = $signature;
                $data['specialPizzas'] = $special;
                $data['popularItems'] = $popularItems;
            }

            $offerCards = DB::table('picture')->where('isActive', 1)->whereNotNull('image')->get();

            $data['offerCards'] = $offerCards->map(function ($item) {
                return [
                    'title' => $item->title,
                    'link' => $item->link ?? "",
                    'picture' => url('uploads/picture/' . $item->image)
                ];
            });

            return response()->json(["status" => 200, "message" => "Data found", "data" => $data], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 300, "message" => "No Data found " . $ex->getMessage()], 400);
        }
    }

    public function get_latest_version(Request $r)
    {
        try {
            $data = [];
            $settingIOS = DB::table("settings")
                ->select("settings.*")
                ->where("settings.code", "STG_6")
                ->first();
            $settingAndroid = DB::table("settings")
                ->select("settings.*")
                ->where("settings.code", "STG_5")
                ->first();
            if (!empty($settingAndroid)) {
                $android = [
                    "settingCode" => $settingAndroid->code,
                    "settingName" => $settingAndroid->settingName,
                    "settingValue" => $settingAndroid->settingValue,
                    "isUpdateCompulsory" => $settingAndroid->isUpdateCompulsory
                ];
                $data["android"] = $android;
            }
            if (!empty($settingIOS)) {
                $ios = [
                    "settingCode" => $settingIOS->code,
                    "settingName" => $settingIOS->settingName,
                    "settingValue" => $settingIOS->settingValue,
                    "isUpdateCompulsory" => $settingIOS->isUpdateCompulsory
                ];
                $data["ios"] = $ios;
            }
            return response()->json(["status" => 200, "message" => "Data found", "result" => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 400, 'message' => 'Something went to wrong'], 400);
        }
    }

    public function set_version(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'settingCode' => 'required',
                'settingValue' => 'required',
                'isUpdateCompulsory' => 'required'
            ]);
            if ($validator->fails()) {
                $response = [
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 200);
            }

            $data = [
                "settingValue" => $r->settingValue,
                "isUpdateCompulsory" => $r->isUpdateCompulsory
            ];
            $result = DB::table("settings")
                ->where("code", $r->settingCode)
                ->update($data);
            if ($result == true) {
                return response()->json(["status" => 200, "message" => "Setting is updated."], 200);
            }
            return response()->json(["status" => 300, "message" => "Setting is not updated."], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 400, 'message' => 'Something went to wrong'], 400);
        }
    }

    //date:16-dec-2024
    //zipcode and storecode is serviceable
    public function check_zipcode_serviceable(Request $r)
    {
        try {
            $input = $r->all();
            $rules = [
                'zipcode' => 'required|regex:/^[ABCEGHJKLMNPRSTVXY]\d[A-Z]\d[A-Z]\d$/i',
                'storeCode' => 'nullable|string',
            ];
            $messages = [
                'zipcode.required' => 'Postal Code is required',
                'zipcode.regex' => 'Enter valid postal code.',
            ];

            $validator = Validator::make($input, $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ], 500);
            }

            $zipcode = $r->zipcode;
            $storeLocation = $r->storeCode;

            // Check if zipcode is in the list and active
            /*$result = Zipcode::where('isActive', 1)->where('zipcode', $zipcode)->first();
        if (!empty($result)) {
            return response()->json([
                "status" => 200,
                "message" => "Zipcode is serviceable",
                'deliverable' => true
            ], 200);
        }*/

            // Check if storeLocation is provided
            if (!empty($storeLocation)) {
                $storeZipcodes = ZipCode::where('storeCode', $storeLocation)
                    ->where('isDelete', 0)
                    ->pluck('zipcode')
                    ->toArray();

                if (in_array($zipcode, $storeZipcodes)) {
                    return response()->json([
                        "status" => 200,
                        "message" => "Zipcode is deliverable for the given store location",
                        'deliverable' => true
                    ], 200);
                }
            }

            // If neither condition is satisfied
            return response()->json([
                "status" => 200,
                "message" => "Zipcode is not serviceable or deliverable",
                'deliverable' => false
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => 400,
                'message' => $ex->getMessage()
            ], 400);
        }
    }

    //dynamic slider entries
    public function dynamicSlider()
    {
        try {
            $sliderArr = [];

            // Fetch all slider data at once
            $sliderData = DynamicSlider::all();

            // If there's any slider data, process it
            if ($sliderData->isNotEmpty()) {

                // Process slider data
                foreach ($sliderData as $data) {
                    $sliderArr[] = [
                        "code" => $data->code,
                        "subTitle" => $data->subTitle ?? "100% Vegetarian",
                        "background_image" => url('uploads/slider-background/' . $data->background_image),
                        "background_image_md" => url('uploads/slider-background/' . $data->background_image_md),
                        "background_image_sm" => url('uploads/slider-background/' . $data->background_image_sm),
                        "title" => $data->title
                    ];
                }
            }
            return response()->json(["status" => 200, 'message' => 'success', 'data' => $sliderArr], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }

    public function searchProducts(Request $request)
    {

        try {
            $keyword = trim($request->search);
            $ttl = 30;
            $commanArray = Cache::remember('products-search-' . $keyword, $ttl, function () use ($keyword) {

                $dips = Dips::where('dips', 'like', '%' . $keyword . '%')
                    ->where('isActive', 1)
                    ->orderBy('id', 'DESC')
                    ->limit(2)
                    ->get();

                $drinks = Softdrinks::where('softdrinks', 'like', '%' . $keyword . '%')
                    ->where('isActive', 1)
                    ->orderBy('id', 'DESC')
                    ->limit(2)
                    ->get();

                $sides = SidesMaster::where('sidename', 'like', '%' . $keyword . '%')
                    ->where('isActive', 1)
                    ->orderBy('id', 'DESC')
                    ->limit(2)
                    ->get();

                $other = Pizzas::where('pizza_name', 'like', '%' . $keyword . '%')
                    ->where('isActive', 1)
                    ->orderBy('id', 'DESC')
                    ->limit(2)
                    ->get();

                $signature = SignaturePizza::where('pizza_name', 'like', '%' . $keyword . '%')
                    ->where('isActive', 1)
                    ->orderBy('id', 'DESC')
                    ->limit(2)
                    ->get();

                $commanArray = [];

                foreach ($dips as $item) {
                    $path = $item->dipsImage ? url("uploads/dips/" . $item->dipsImage) : url("uploads/sample_dips.png");
                    $commanArray[] = [
                        "code" => $item->code,
                        "name" => $item->dips,
                        "image" => $path,
                        "ratings" => $item->ratings,
                        "productType" => "dips"
                    ];
                }

                foreach ($drinks as $item) {
                    $path = $item->softDrinkImage ? url("uploads/softdrinks/" . $item->softDrinkImage) : url("uploads/sample_drinks.png");
                    $commanArray[] = [
                        "code" => $item->code,
                        "name" => $item->softdrinks,
                        "image" => $path,
                        "ratings" => $item->ratings,
                        "productType" => "drinks"
                    ];
                }

                foreach ($sides as $item) {
                    $path = $item->image ? url("uploads/sides/" . $item->image) : url("uploads/sample_sides.png");
                    $commanArray[] = [
                        "code" => $item->code,
                        "name" => $item->sidename,
                        "image" => $path,
                        "ratings" => $item->ratings,
                        "productType" => "sides"
                    ];
                }

                foreach ($other as $item) {
                    $path = $item->pizza_image ? url("uploads/signature-pizza/" . $item->pizza_image) : url("uploads/sample_pizza.jpg");
                    $commanArray[] = [
                        "code" => $item->code,
                        "name" => $item->pizza_name,
                        "image" => $path,
                        "ratings" => $item->ratings,
                        "productType" => "other"
                    ];
                }

                foreach ($signature as $item) {
                    $path = $item->pizza_image ? url("uploads/signature-pizza/" . $item->pizza_image) : url("uploads/sample_pizza.jpg");
                    $commanArray[] = [
                        "code" => $item->code,
                        "name" => $item->pizza_name,
                        "image" => $path,
                        "ratings" => $item->ratings,
                        "productType" => "signature"
                    ];
                }

                return $commanArray;
            });

            return response()->json(["status" => 200, 'message' => 'success', 'data' => $commanArray], 200);
        } catch (\Throwable $th) {
            return response()->json(["status" => 400, 'message' => $th->getMessage()], 400);
        }
    }
}
