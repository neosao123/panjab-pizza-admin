<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Classes\Helper;
// Models
use App\Models\GlobalModel;
use App\Models\ApiModel;
use App\Models\Setting;

class CartController extends Controller
{
    public function __construct(GlobalModel $model, ApiModel $apimodel)
    {
        $this->model = $model;
        $this->apimodel = $apimodel;
    }

    // Verify Cart
    // Developer: ShreyasM, Working Date: 9oct2024
    public function verify_cart(Request $r)
    {
        try {
            $rules = [
                'cart_json'                              => 'required|array',
                'cart_json.storeCode'                    => 'nullable|string|max:255',
                'cart_json.products'                     => 'required|array|min:1',
                'cart_json.products.*.id'                => 'required|string|max:255',
                'cart_json.products.*.productCode'       => 'required|string|max:255',
                'cart_json.products.*.productName'       => 'required|string|max:255',
                'cart_json.products.*.productType'       => 'required|string|max:255',
                'cart_json.products.*.config'            => 'nullable|array',
                'cart_json.products.*.quantity'          => 'required|integer|min:1',
                'cart_json.products.*.price'             => 'required|numeric|min:0',
                'cart_json.products.*.amount'            => 'required|numeric|min:0',
                'cart_json.products.*.taxPer'            => 'nullable|numeric|min:0',
                'cart_json.products.*.pizzaSize'         => 'nullable|string|max:50',
                'cart_json.products.*.comments'          => 'nullable|string|max:255',
                'cart_json.subTotal'                     => 'nullable|numeric|min:0',
                'cart_json.discountAmount'               => 'nullable|numeric|min:0',
                'cart_json.taxPer'                       => 'nullable|numeric|min:0',
                'cart_json.taxAmount'                    => 'nullable|numeric|min:0',
                'cart_json.deliveryCharges'              => 'nullable|numeric|min:0',
                'cart_json.extraDeliveryCharges'         => 'nullable|numeric|min:0',
                'cart_json.grandTotal'                   => 'nullable|numeric|min:0',
            ];
            $messages = [
                'cart_json.required' => 'The cart data is required.',
                'cart_json.array' => 'The cart data must be an array.',

                'cart_json.storeCode.string' => 'The store code must be a string.',
                'cart_json.storeCode.max' => 'The store code may not be greater than 255 characters.',

                'cart_json.products.required' => 'The products are required.',
                'cart_json.products.array' => 'The products must be an array.',
                'cart_json.products.min' => 'At least one product is required in the cart.',

                'cart_json.products.*.id.required' => 'The product ID is required.',
                'cart_json.products.*.id.string' => 'The product ID must be a string.',
                'cart_json.products.*.id.max' => 'The product ID may not be greater than 255 characters.',

                'cart_json.products.*.productCode.required' => 'The product code is required.',
                'cart_json.products.*.productCode.string' => 'The product code must be a string.',
                'cart_json.products.*.productCode.max' => 'The product code may not be greater than 255 characters.',

                'cart_json.products.*.productName.required' => 'The product name is required.',
                'cart_json.products.*.productName.string' => 'The product name must be a string.',
                'cart_json.products.*.productName.max' => 'The product name may not be greater than 255 characters.',

                'cart_json.products.*.productType.required' => 'The product type is required.',
                'cart_json.products.*.productType.string' => 'The product type must be a string.',
                'cart_json.products.*.productType.max' => 'The product type may not be greater than 255 characters.',

                'cart_json.products.*.config.array' => 'The config must be an array.',

                'cart_json.products.*.quantity.required' => 'The quantity is required.',
                'cart_json.products.*.quantity.integer' => 'The quantity must be an integer.',
                'cart_json.products.*.quantity.min' => 'The quantity must be at least 1.',

                'cart_json.products.*.price.required' => 'The price is required.',
                'cart_json.products.*.price.numeric' => 'The price must be a number.',
                'cart_json.products.*.price.min' => 'The price must be at least 0.',

                'cart_json.products.*.amount.required' => 'The amount is required.',
                'cart_json.products.*.amount.numeric' => 'The amount must be a number.',
                'cart_json.products.*.amount.min' => 'The amount must be at least 0.',

                'cart_json.products.*.taxPer.numeric' => 'The tax percentage must be a number.',
                'cart_json.products.*.taxPer.min' => 'The tax percentage must be at least 0.',

                'cart_json.products.*.pizzaSize.string' => 'The pizza size must be a string.',
                'cart_json.products.*.pizzaSize.max' => 'The pizza size may not be greater than 50 characters.',

                'cart_json.products.*.comments.string' => 'The comments must be a string.',
                'cart_json.products.*.comments.max' => 'The comments may not be greater than 255 characters.',

                //'cart_json.subTotal.required' => 'The subtotal is required.',
                'cart_json.subTotal.numeric' => 'The subtotal must be a number.',
                'cart_json.subTotal.min' => 'The subtotal must be at least 0.',

                'cart_json.discountAmount.numeric' => 'The discount amount must be a number.',
                'cart_json.discountAmount.min' => 'The discount amount must be at least 0.',

                'cart_json.taxPer.numeric' => 'The tax percentage must be a number.',
                'cart_json.taxPer.min' => 'The tax percentage must be at least 0.',

                'cart_json.taxAmount.numeric' => 'The tax amount must be a number.',
                'cart_json.taxAmount.min' => 'The tax amount must be at least 0.',

                'cart_json.deliveryCharges.numeric' => 'The delivery charges must be a number.',
                'cart_json.deliveryCharges.min' => 'The delivery charges must be at least 0.',

                'cart_json.extraDeliveryCharges.numeric' => 'The extra delivery charges must be a number.',
                'cart_json.extraDeliveryCharges.min' => 'The extra delivery charges must be at least 0.',

                //'cart_json.grandTotal.required' => 'The grand total is required.',
                'cart_json.grandTotal.numeric' => 'The grand total must be a number.',
                'cart_json.grandTotal.min' => 'The grand total must be at least 0.',

            ];
            $validate = Validator::make($r->all(), $rules, $messages);
            if ($validate->fails()) {
                Log::error("Validation Error", [
                    "exception",
                    'user_id' => Auth::guard('admin')->id() ?? "",
                    'function' => __FUNCTION__,
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'path' =>  __FILE__,
                    'exception' => $validate->errors()->first(),
                    'request' => request()->all(),
                ]);
                return response()->json(['status' => 500, 'message' => $validate->errors()->first()], 200);
            }

            $nonRegularToppingCount = 1;

            $settings = Setting::where('code', 'STG_7')->first();
            if ($settings) {
                $nonRegularToppingCount = $settings->settingValue;
            }

            $helper = new Helper();
            $verify_sub_total = 0;
            foreach ($r->cart_json['products'] as $product) {
                $dipsAmount = 0;
                $drinksAmount = 0;
                if ($product['productType'] == "dips") {
                    $dipsAmount = $helper->dips_calculations($product);
                    $verify_sub_total += $dipsAmount;
                }
                if ($product['productType'] == "drinks") {
                    $drinksAmount = $helper->drinks_calculations($product);
                    $verify_sub_total += $drinksAmount;
                }
                if ($product['productType'] == "side") {
                    $sidesAmount = $helper->sides_calculations($product);
                    $verify_sub_total += $sidesAmount;
                }
                if ($product['productType'] == "custom_pizza") {
                    $custompizzaAmount = $helper->custom_pizza_calculations($product);
                    $verify_sub_total += $custompizzaAmount;
                }
                if ($product['productType'] == "special_pizza") {
                    $specialpizzaAmount = $helper->special_pizza_calculations($product,$nonRegularToppingCount);
                    $verify_sub_total += $specialpizzaAmount;
                }
                if ($product['productType'] == "signature_pizza") {
                    $signaturepizzaAmount = $helper->signature_pizza_calculations($product);
                    $verify_sub_total += $signaturepizzaAmount;
                }
                if ($product['productType'] == "other_pizza") {
                    $otherpizzaAmount = $helper->other_pizza_calculations($product);
                    $verify_sub_total += $otherpizzaAmount;
                }
            }
            $data = $helper->grand_total_calculations($verify_sub_total, $r->cart_json);
            return response()->json(['status' => 200, 'msg' => 'success', 'data' => $data], 200);
        } catch (\Exception $exception) {
            Log::error("Validation Error", [
                "exception",
                'user_id' => Auth::guard('admin')->id() ?? "",
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' =>  __FILE__,
                'exception' => $exception->getMessage(),
                'request' => request()->all(),
            ]);
            return response()->json(['status' => 400, 'message' => $exception->getMessage()], 400);
        }
    }
}
