<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\CrustType;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\Setting;
use App\Models\ApiModel;
use App\Models\Users;
use App\Models\Specialoffer;
use App\Models\Softdrinks;
use App\Models\SidesMaster;
use App\Models\SidelineEntries;
use App\Models\Crust;
use App\Models\Cheese;
use App\Models\Specialbases;
use App\Models\Sauce;
use App\Models\Spices;
use App\Models\Cook;
use App\Models\SignaturePizza;
use App\Models\Specialofferlineentries;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Config;

class SpecialOfferController extends Controller
{
    public function __construct(GlobalModel $model, ApiModel $apimodel)
    {
        $this->model = $model;
        $this->apimodel = $apimodel;
    }

    public function list(Request $r)
    {
        try {
            $specialOfferArray = [];
            $query = Specialoffer::select("specialoffer.*")
                ->whereNotNull("pizza_prices")
                ->where("isActive", 1);
            if ($r->has('cashier') && $r->cashier == 1) {
            } else {
                $query->where("showOnClient", 1);
            }

            $query->orderByRaw('
                	CASE
						WHEN dealType = "pickupdeal" THEN 1
						WHEN dealType = "deliverydeal" THEN 2
						WHEN dealType = "otherdeal" THEN 3
                    	ELSE 4
                	END
            	')
                ->orderByRaw('
                	CASE
                    	WHEN dealType IN ("pickupdeal", "deliverydeal", "otherdeal") THEN CAST(noofToppings AS SIGNED)
                    	ELSE NULL
                	END
            	');

            $getSpecialOffer = $query->orderBy('id', 'DESC')->get();

            if ($getSpecialOffer && count($getSpecialOffer) > 0) {
                foreach ($getSpecialOffer as $item) {
                    /*$currentDate = now();
                    $isLimitedOfferValid = true;
                    if ($item->limited_offer == 1) {
                        $startDate = Carbon::parse($item->start_date);
                        $endDate = Carbon::parse($item->end_date);
                        if ($currentDate->lt($startDate) || $currentDate->gt($endDate)) {
                            $isLimitedOfferValid = false;
                        }
                    }

                    if ($item->limited_offer == 1 && !$isLimitedOfferValid) {
                        continue;
                    }*/

                    $path = url("uploads/pizza.jpg");
                    if ($item->specialofferphoto != "" && $item->specialofferphoto != null) {
                        $path = url("uploads/specialoffer/" . $item->specialofferphoto);
                    }
                    $data = [
                        "code" => $item->code,
                        "name" => $item->name,
                        "subtitle" => $item->subtitle,
                        "dealType" => $item->dealType,
                        "description" => $item->description ?? "",
                        "noofToppings" => $item->noofToppings ?? "",
                        "noofPizzas" => $item->noofPizza ?? "",
                        "noofDips" => $item->noofDips ?? "",
                        "noofSides" => $item->noofSides ?? "",
                        "pizza_prices" => json_decode($item->pizza_prices, true) ?? "",
                        "image" => $path,
                        "showOnClient" => $item->showOnClient ?? 0,
                        "limitedOffer" => $item->limited_offer ?? "",
                        "limitedOfferStartDate" => $item->start_date ?? "",
                        "limitedOfferEndDate" => $item->end_date ?? "",
                        "ratings" => $item->ratings
                    ];
                    array_push($specialOfferArray, $data);
                }
                return response()->json(["message" => "Data found", "data" => $specialOfferArray], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function searchableSpecialDeals(Request $r)
    {
        try {
            $query = Specialoffer::select("specialoffer.*")
                ->whereNotNull("pizza_prices")
                ->where("isActive", 1);

            if ($r->has('search') && !empty($r->search)) {
                $query->where(function ($query) use ($r) {
                    $query->where("name", "like", "%" . $r->search . "%")
                        ->orWhere("noofPizza", "like", "%" . $r->search . "%");
                });
            }

            $query->orderByRaw('
                CASE
                    WHEN dealType = "pickupdeal" THEN 1
                    WHEN dealType = "deliverydeal" THEN 2
                    WHEN dealType = "otherdeal" THEN 3
                    ELSE 4
                END
            ')
                ->orderByRaw('
                CASE
                    WHEN dealType IN ("pickupdeal", "deliverydeal", "otherdeal") THEN CAST(noofToppings AS SIGNED)
                    ELSE NULL
                END
            ');

            $getSpecialOffer = $query->orderBy('id', 'DESC')->get();

            if ($getSpecialOffer->isEmpty()) {
                return response()->json(["status" => 300, "message" => "No Data found"], 200);
            }
            $specialOfferArray = [];
            if ($getSpecialOffer->groupBy('dealType')) {
                foreach ($getSpecialOffer->groupBy('dealType') as $type => $specialDealsByType) {
                    $specialDeals = [];
                    if (!empty($specialDealsByType)) {
                        foreach ($specialDealsByType as $item) {
                            $path = url("uploads/pizza.jpg");
                            if ($item->specialofferphoto != "" && $item->specialofferphoto != null) {
                                $path = url("uploads/specialoffer/" . $item->specialofferphoto);
                            }
                            $data = [
                                "code" => $item->code,
                                "name" => $item->name,
                                "subtitle" => $item->subtitle,
                                "dealType" => $item->dealType,
                                "description" => $item->description ?? "",
                                "noofToppings" => $item->noofToppings ?? "",
                                "noofPizzas" => $item->noofPizza ?? "",
                                "noofDips" => $item->noofDips ?? "",
                                "noofSides" => $item->noofSides ?? "",
                                "pizza_prices" => json_decode($item->pizza_prices, true) ?? "",
                                "image" => $path,
                                "showOnClient" => $item->showOnClient ?? 0,
                                "limitedOffer" => $item->limited_offer ?? "",
                                "limitedOfferStartDate" => $item->start_date ?? "",
                                "limitedOfferEndDate" => $item->end_date ?? "",
                                "ratings" => $item->ratings
                            ];
                            array_push($specialDeals, $data);
                        }
                    }
                    $specialOfferArray[] = [
                        "delaType" => $type,
                        "specialDeals" => $specialDeals
                    ];
                }
            }
            return response()->json(["message" => "Data found", "data" => $specialOfferArray], 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => 400, 'message' => $ex->getMessage()], 400);
        }
    }


    public function show($code)
    {
        try {

            if (!$code && $code != "") {
                $response = [
                    "message" => "Invalid request"
                ];
                return response()->json($response, 500);
            }
            $getSpecialDetails = Specialoffer::select("specialoffer.*")
                ->where("specialoffer.code", $code)
                ->whereNotNull("pizza_prices")
                ->where("isActive", 1)
                ->first();

            if (!empty($getSpecialDetails)) {
                /* $currentDate = now();
                $isLimitedOfferValid = true;
                if ($getSpecialDetails->limited_offer == 1) {
                    $startDate = Carbon::parse($getSpecialDetails->start_date);
                    $endDate = Carbon::parse($getSpecialDetails->end_date);
                    if ($currentDate->lt($startDate) || $currentDate->gt($endDate)) {
                        $isLimitedOfferValid = false;
                    }
                }

                if ($getSpecialDetails->limited_offer == 1 && !$isLimitedOfferValid) {
                    return response()->json(["message" => "Data not found", $getSpecialDetails], 200);
                }*/

                $data = [];
                $popsData = [];
                $bottleData = [];
                $sideArray = [];
                $specialbasesArray = [];
                $crustArray = [];
                $crustTypeArray = [];
                $cheeseArray = [];
                $freeSideArray = [];
                $sauce = [];
                $spices = [];
                $cooks = [];

                $path = url("uploads/pizza.jpg");
                if ($getSpecialDetails->specialofferphoto != "" && $getSpecialDetails->specialofferphoto != null) {
                    $path = url("uploads/specialoffer/" . $getSpecialDetails->specialofferphoto);
                }
                $type = json_decode($getSpecialDetails->type, true);

                if (!empty($type) && $type != null && $type != "") {
                    $freesides = SidesMaster::select("sidemaster.*", "specialofferlineentries.sidemasterCode", "specialofferlineentries.sidelineentries")
                        ->join("specialofferlineentries", "specialofferlineentries.sidemasterCode", "=", "sidemaster.code", "left")
                        ->where("specialofferlineentries.specialOfferCode", $code)
                        ->where("sidemaster.isActive", 1)
                        ->where("specialofferlineentries.isActive", 1)
                        ->where("specialofferlineentries.isDelete", 0)
                        ->whereIn("sidemaster.type", $type)
                        ->get();

                    if ($freesides && count($freesides) > 0) {
                        foreach ($freesides as $items) {
                            $freesideLineData = [];
                            $freesideImageUrl = "";
                            if ($items->image != "" && $items->image != null) {
                                $freesideImageUrl = url("uploads/sides/" . $items->image);
                            }
                            $freegetPricing = SidelineEntries::select("sidelineentries.*")
                                ->join("specialofferlineentries", "specialofferlineentries.sidelineentries", "=", "sidelineentries.code", "left")
                                ->where("specialofferlineentries.sidelineentries", $items->sidelineentries)
                                ->first();
                            if (!empty($freegetPricing)) {
                                $freesideLine = ["code" => $freegetPricing->code, "size" => $freegetPricing->size, "price" => $freegetPricing->price];
                                array_push($freesideLineData, $freesideLine);
                            }

                            $freesidedata = [
                                "code" => $items->code,
                                "sideName" => $items->sidename,
                                "type" => $items->type,
                                "image" => $freesideImageUrl,
                                "lineEntries" => $freesideLineData,
                            ];

                            array_push($freeSideArray, $freesidedata);
                        }
                    }
                }

                if ($getSpecialDetails->pops != null && $getSpecialDetails->pops != "") {
                    $typeDrinks = [];
                    $pops = Softdrinks::select("softdrinks.*")
                        ->where("code", $getSpecialDetails->pops)
                        ->first();
                    if (!empty($pops)) {
                        $pospImage = "";
                        if ($pops->softDrinkImage != "" && $pops->softDrinkImage != null) {
                            $pospImage = url("uploads/softdrinks/" . $pops->softDrinkImage);
                        }

                        $typeDrinks = [];
                        if ($pops->code == "SFD_5") {
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

                        $popArr = [
                            'code' => $pops->code,
                            'softDrinkName' => $pops->softdrinks,
                            'price' => $pops->price,
                            'image' => $pospImage,
                            'drinkType' => $typeDrinks,
                        ];

                        $popsData[] = $popArr;
                    }
                }
                if ($getSpecialDetails->bottle != null && $getSpecialDetails->bottle != "") {
                    $typeDrinks = [];
                    $bottle = Softdrinks::select("softdrinks.*")
                        ->where("code", $getSpecialDetails->bottle)
                        ->first();
                    if (!empty($bottle)) {
                        $bottleImage = "";
                        if ($bottle->softDrinkImage != "" && $bottle->softDrinkImage != null) {
                            $bottleImage = url("uploads/softdrinks/" . $bottle->softDrinkImage);
                        }

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

                        $bottleArr = [
                            'code' => $bottle->code,
                            'softDrinkName' => $bottle->softdrinks,
                            'price' => $bottle->price,
                            'image' => $bottleImage,
                            'drinkType' => $typeDrinks,
                        ];

                        $bottleData[] = $bottleArr;
                    }
                }

                $data["code"] = $getSpecialDetails->code;
                $data["name"] = $getSpecialDetails->name;
                $data["subtitle"] = $getSpecialDetails->subtitle;
                $data["description"] = $getSpecialDetails->description ?? "";
                $data["noofToppings"] = $getSpecialDetails->noofToppings ?? "";
                $data["ratings"] = $getSpecialDetails->ratings ?? "4.5";
                $data["noofDips"] = $getSpecialDetails->noofDips ?? "";
                $data["noofSides"] = $getSpecialDetails->noofSides ?? "";
                $data["noofPizzas"] = $getSpecialDetails->noofPizza ?? "";
                $data["pizza_prices"] = json_decode($getSpecialDetails->pizza_prices, true) ?? "";
                $data['showOnClient'] = $getSpecialDetails->showOnClient ?? 0;
                $data['noofDrinks'] = (sizeof($bottleData) > 0 || sizeof($popsData) > 0) ? 1 : 0;
                $data["image"] = $path;
                $data["freesides"] = $freeSideArray;
                $data["sides"] = $sideArray;
                $data["pops"] = $popsData;
                $data["bottle"] = $bottleData;

                /*
                $crust = Crust::where('isActive', 1)->orderBy("id", "ASC")->get();
                $crustType = CrustType::where('isActive', 1)->orderBy("id", "ASC")->get();
                $cheese = Cheese::where('isActive', 1)->orderBy("id", "ASC")->get();
                $specialbases = Specialbases::where('isActive', 1)->orderBy("id", "ASC")->get();
                $cook = Cook::where('isActive', 1)->orderBy("id", "ASC")->get();
                $spicy = Spices::where('isActive', 1)->orderBy("id", "ASC")->get();
                $sauces = Sauce::where('isActive', 1)->orderBy("id", "ASC")->get();

                  if ($cheese && count($cheese) > 0) {
                    foreach ($cheese as $item) {
                        $cheesedata = ["code" => $item->code, "cheeseName" => $item->cheese, "price" => $item->price];
                        array_push($cheeseArray, $cheesedata);
                    }
                }
                if ($crust && count($crust) > 0) {
                    foreach ($crust as $item) {
                        $crustdata = ["code" => $item->code, "crustName" => $item->crust, "price" => $item->price];
                        array_push($crustArray, $crustdata);
                    }
                }

                if ($crustType && count($crustType) > 0) {
                    foreach ($crustType as $item) {
                        $crustTypedata = ["crustTypeCode" => $item->code, "crustType" => $item->crustType, "price" => $item->price];
                        array_push($crustTypeArray, $crustTypedata);
                    }
                }

                if ($specialbases && count($specialbases) > 0) {
                    foreach ($specialbases as $item) {
                        $specialbasesdata = ["code" => $item->code, "specialbaseName" => $item->specialbase, "price" => $item->price];
                        array_push($specialbasesArray, $specialbasesdata);
                    }
                }

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

                $data["cheese"] = $cheeseArray;
                $data["crust"] = $crustArray;
                $data["crustType"] = $crustTypeArray;
                $data["specialbases"] = $specialbasesArray;
                $data["cook"] = $cooks;
                $data["sauce"] = $sauce;
                $data["spices"] = $spices;
                */

                $data["dealType"] = $getSpecialDetails->dealType;
                $data["limitedOffer"] = $getSpecialDetails->limited_offer ?? "";
                $data["limitedOfferStartDate"] = $getSpecialDetails->start_date ?? "";
                $data["limitedOfferEndDate"] = $getSpecialDetails->end_date ?? "";

                $ttl = 60 * 5;
                $premiumToppingSettings = Cache::remember('premium_setting', $ttl, function () {
                    return Setting::where('code', 'STG_7')->first()->settingValue ?? 1;
                });

                $signaturePizzasArray = SignaturePizza::where('isDelete', '!=', 1)->get()->map(function ($item) use ($premiumToppingSettings) {
                    $path = url("uploads/pizza.jpg");
                    if ($item->pizza_image != "" && $item->pizza_image != null) {
                        $path = url("uploads/signatture-pizza/" . $item->pizza_image);
                    }

                    $toppings2 = [];
                    foreach ($item->topping_as_2 as $topping) {
                        $toppings2[] = [
                            'code' => $topping['code'],
                            'name' => $topping['title'],
                            'count' => $premiumToppingSettings,
                        ];
                    }

                    $toppings1 = [];
                    foreach ($item->topping_as_1 as $topping) {
                        $toppings1[] = [
                            'code' => $topping['code'],
                            'name' => $topping['title'],
                            'count' => 1,
                        ];
                    }

                    $toppingFree = [];
                    $indianToppings = true;
                    if (count($item->topping_as_free) < 6) {
                        $indianToppings = false;
                        foreach ($item->topping_as_free as $topping) {
                            $toppingFree[] = [
                                'code' => $topping['code'],
                                'name' => $topping['title'],
                                'count' => 1,
                            ];
                        }
                    }

                    $toppings = array_merge($toppings2, $toppings1);

                    return [
                        'code' => $item->code,
                        'pizzaName' => $item->pizza_name,
                        'pizzaImage' => $path,
                        'toppings' => $toppings,
                        'freeToppings' => $toppingFree,
                        'indianToppings' => $indianToppings,
                    ];
                });

                $data['signaturePizzas'] = $signaturePizzasArray;

                return response()->json(["message" => "Data found", "data" => $data], 200);
            }
            return response()->json(["message" => "Data not found"], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function showWeb($code)
    {
        try {

            if (!$code && $code != "") {
                $response = [
                    "message" => "Invalid request"
                ];
                return response()->json($response, 500);
            }
            $getSpecialDetails = Specialoffer::select("specialoffer.*")
                ->where("specialoffer.code", $code)
                ->whereNotNull("pizza_prices")
                ->where("isActive", 1)
                ->first();

            if (!empty($getSpecialDetails)) {
                /* $currentDate = now();
                $isLimitedOfferValid = true;
                if ($getSpecialDetails->limited_offer == 1) {
                    $startDate = Carbon::parse($getSpecialDetails->start_date);
                    $endDate = Carbon::parse($getSpecialDetails->end_date);
                    if ($currentDate->lt($startDate) || $currentDate->gt($endDate)) {
                        $isLimitedOfferValid = false;
                    }
                }

                if ($getSpecialDetails->limited_offer == 1 && !$isLimitedOfferValid) {
                    return response()->json(["message" => "Data not found", $getSpecialDetails], 200);
                }*/

                $data = [];
                $popsData = [];
                $bottleData = [];
                $sideArray = [];
                $specialbasesArray = [];
                $crustArray = [];
                $crustTypeArray = [];
                $cheeseArray = [];
                $freeSideArray = [];
                $sauce = [];
                $spices = [];
                $cooks = [];

                $path = url("uploads/pizza.jpg");
                if ($getSpecialDetails->specialofferphoto != "" && $getSpecialDetails->specialofferphoto != null) {
                    $path = url("uploads/specialoffer/" . $getSpecialDetails->specialofferphoto);
                }
                $type = json_decode($getSpecialDetails->type, true);

                if (!empty($type) && $type != null && $type != "") {
                    $freesides = SidesMaster::select("sidemaster.*", "specialofferlineentries.sidemasterCode", "specialofferlineentries.sidelineentries")
                        ->join("specialofferlineentries", "specialofferlineentries.sidemasterCode", "=", "sidemaster.code", "left")
                        ->where("specialofferlineentries.specialOfferCode", $code)
                        ->where("sidemaster.isActive", 1)
                        ->where("specialofferlineentries.isActive", 1)
                        ->where("specialofferlineentries.isDelete", 0)
                        ->whereIn("sidemaster.type", $type)
                        ->get();

                    if ($freesides && count($freesides) > 0) {
                        foreach ($freesides as $items) {
                            $freesideLineData = [];
                            $freesideImageUrl = "";
                            if ($items->image != "" && $items->image != null) {
                                $freesideImageUrl = url("uploads/sides/" . $items->image);
                            }
                            $freegetPricing = SidelineEntries::select("sidelineentries.*")
                                ->join("specialofferlineentries", "specialofferlineentries.sidelineentries", "=", "sidelineentries.code", "left")
                                ->where("specialofferlineentries.sidelineentries", $items->sidelineentries)
                                ->first();
                            if (!empty($freegetPricing)) {
                                $freesideLine = ["code" => $freegetPricing->code, "size" => $freegetPricing->size, "price" => $freegetPricing->price];
                                array_push($freesideLineData, $freesideLine);
                            }

                            $freesidedata = [
                                "code" => $items->code,
                                "sideName" => $items->sidename,
                                "type" => $items->type,
                                "image" => $freesideImageUrl,
                                "lineEntries" => $freesideLineData,
                            ];

                            array_push($freeSideArray, $freesidedata);
                        }
                    }
                }

                if ($getSpecialDetails->pops != null && $getSpecialDetails->pops != "") {
                    $typeDrinks = [];
                    $pops = Softdrinks::select("softdrinks.*")
                        ->where("code", $getSpecialDetails->pops)
                        ->first();
                    if (!empty($pops)) {
                        $pospImage = "";
                        if ($pops->softDrinkImage != "" && $pops->softDrinkImage != null) {
                            $pospImage = url("uploads/softdrinks/" . $pops->softDrinkImage);
                        }

                        $typeDrinks = [];
                        if ($pops->code == "SFD_5") {
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

                        $popArr = [
                            'code' => $pops->code,
                            'softDrinkName' => $pops->softdrinks,
                            'price' => $pops->price,
                            'image' => $pospImage,
                            'drinkType' => $typeDrinks,
                        ];

                        $popsData[] = $popArr;
                    }
                }
                if ($getSpecialDetails->bottle != null && $getSpecialDetails->bottle != "") {
                    $typeDrinks = [];
                    $bottle = Softdrinks::select("softdrinks.*")
                        ->where("code", $getSpecialDetails->bottle)
                        ->first();
                    if (!empty($bottle)) {
                        $bottleImage = "";
                        if ($bottle->softDrinkImage != "" && $bottle->softDrinkImage != null) {
                            $bottleImage = url("uploads/softdrinks/" . $bottle->softDrinkImage);
                        }

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

                        $bottleArr = [
                            'code' => $bottle->code,
                            'softDrinkName' => $bottle->softdrinks,
                            'price' => $bottle->price,
                            'image' => $bottleImage,
                            'drinkType' => $typeDrinks,
                        ];

                        $bottleData[] = $bottleArr;
                    }
                }


                $crust = Crust::where('isActive', 1)->orderBy("id", "ASC")->get();
                $crustType = CrustType::where('isActive', 1)->orderBy("id", "ASC")->get();
                $cheese = Cheese::where('isActive', 1)->orderBy("id", "ASC")->get();
                $specialbases = Specialbases::where('isActive', 1)->orderBy("id", "ASC")->get();
                $cook = Cook::where('isActive', 1)->orderBy("id", "ASC")->get();
                $spicy = Spices::where('isActive', 1)->orderBy("id", "ASC")->get();
                $sauces = Sauce::where('isActive', 1)->orderBy("id", "ASC")->get();

                if ($cheese && count($cheese) > 0) {
                    foreach ($cheese as $item) {
                        $cheesedata = ["cheeseCode" => $item->code, "cheeseName" => $item->cheese, "price" => $item->price];
                        array_push($cheeseArray, $cheesedata);
                    }
                }
                if ($crust && count($crust) > 0) {
                    foreach ($crust as $item) {
                        $crustdata = ["crustCode" => $item->code, "crustName" => $item->crust, "price" => $item->price];
                        array_push($crustArray, $crustdata);
                    }
                }

                if ($crustType && count($crustType) > 0) {
                    foreach ($crustType as $item) {
                        $crustTypedata = ["crustTypeCode" => $item->code, "crustType" => $item->crustType, "price" => $item->price];
                        array_push($crustTypeArray, $crustTypedata);
                    }
                }

                if ($specialbases && count($specialbases) > 0) {
                    foreach ($specialbases as $item) {
                        $specialbasesdata = ["specialbaseCode" => $item->code, "specialbaseName" => $item->specialbase, "price" => $item->price];
                        array_push($specialbasesArray, $specialbasesdata);
                    }
                }

                if ($cook && count($cook) > 0) {
                    foreach ($cook as $item) {
                        $data = ["cookCode" => $item->code, "cook" => $item->cook, "price" => $item->price];
                        array_push($cooks, $data);
                    }
                }

                if ($sauces && count($sauces) > 0) {
                    foreach ($sauces as $item) {
                        $data = ["sauceCode" => $item->code, "sauce" => $item->sauce, "price" => $item->price];
                        array_push($sauce, $data);
                    }
                }

                if ($spicy && count($spicy) > 0) {
                    foreach ($spicy as $item) {
                        $data = ["spicyCode" => $item->code, "spicy" => $item->spicy, "price" => $item->price];
                        array_push($spices, $data);
                    }
                }


                $ttl = 60 * 5;
                $premiumToppingSettings = Cache::remember('premium_setting', $ttl, function () {
                    return Setting::where('code', 'STG_7')->first()->settingValue ?? 1;
                });

                $signaturePizzasArray = SignaturePizza::where('isDelete', '!=', 1)->get()->map(function ($item) use ($premiumToppingSettings) {
                    $path = url("uploads/pizza.jpg");
                    if ($item->pizza_image != "" && $item->pizza_image != null) {
                        $path = url("uploads/signatture-pizza/" . $item->pizza_image);
                    }

                    $toppings2 = [];
                    foreach ($item->topping_as_2 as $topping) {
                        $toppings2[] = [
                            'code' => $topping['code'],
                            'name' => $topping['title'],
                            'count' => $premiumToppingSettings,
                        ];
                    }

                    $toppings1 = [];
                    foreach ($item->topping_as_1 as $topping) {
                        $toppings1[] = [
                            'code' => $topping['code'],
                            'name' => $topping['title'],
                            'count' => 1,
                        ];
                    }

                    $toppingFree = [];
                    $indianToppings = true;
                    if (count($item->topping_as_free) < 6) {
                        $indianToppings = false;
                        foreach ($item->topping_as_free as $topping) {
                            $toppingFree[] = [
                                'code' => $topping['code'],
                                'name' => $topping['title'],
                                'count' => 1,
                            ];
                        }
                    }

                    $toppings = array_merge($toppings2, $toppings1);

                    return [
                        'code' => $item->code,
                        'pizzaName' => $item->pizza_name,
                        'pizzaImage' => $path,
                        'toppings' => $toppings,
                        'freeToppings' => $toppingFree,
                        'indianToppings' => $indianToppings,
                        "cheese" => $item->cheese,
                        "crust" => $item->crust,
                        "crustType" => $item->crust_type,
                        "specialbases" => $item->special_base,
                        "cook" => $item->cook,
                        "sauce" => $item->sauce,
                        "spices" => $item->spices,
                    ];
                });

                $data["code"] = $getSpecialDetails->code;
                $data["name"] = $getSpecialDetails->name;
                $data["subtitle"] = $getSpecialDetails->subtitle;
                $data["description"] = $getSpecialDetails->description ?? "";
                $data["noofToppings"] = $getSpecialDetails->noofToppings ?? "";
                $data["ratings"] = $getSpecialDetails->ratings ?? "4.5";
                $data["dealType"] = $getSpecialDetails->dealType;
                $data["limitedOffer"] = $getSpecialDetails->limited_offer ?? "";
                $data["limitedOfferStartDate"] = $getSpecialDetails->start_date ?? "";
                $data["limitedOfferEndDate"] = $getSpecialDetails->end_date ?? "";
                $data["noofDips"] = $getSpecialDetails->noofDips ?? "";
                $data["noofSides"] = $getSpecialDetails->noofSides ?? "";
                $data["noofPizzas"] = $getSpecialDetails->noofPizza ?? "";
                $data["pizza_prices"] = json_decode($getSpecialDetails->pizza_prices, true) ?? "";
                $data['noofDrinks'] = (sizeof($bottleData) > 0 || sizeof($popsData) > 0) ? 1 : 0;
                $data["image"] = $path;
                $data["freesides"] = $freeSideArray;
                $data["pops"] = $popsData;
                $data["bottle"] = $bottleData;
                $data["cheese"] = $cheeseArray;
                $data["crust"] = $crustArray;
                $data["crustType"] = $crustTypeArray;
                $data["specialbases"] = $specialbasesArray;
                $data["cook"] = $cooks;
                $data["sauce"] = $sauce;
                $data["spices"] = $spices;

                $data['signaturePizzas'] = $signaturePizzasArray;

                return response()->json(["message" => "Data found", "data" => $data], 200);
            }
            return response()->json(["message" => "Data not found"], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }
}
