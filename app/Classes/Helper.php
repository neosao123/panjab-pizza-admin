<?php

namespace App\Classes;

// Models
use App\Models\Province;
use App\Models\Setting;
use App\Models\Storelocation;
use Illuminate\Support\Facades\DB;

class Helper
{
    // Developer: ShreyasM, Working Date: 9oct2024
    // For Dips Calculation
    public function dips_calculations($product)
    {
        $verify_dips_amount = $product['price'] * $product['quantity'];
        return $verify_dips_amount;
    }

    // Developer: ShreyasM, Working Date: 9oct2024
    // For Drinks Calculation
    public function drinks_calculations($product)
    {
        $verify_drinks_amount = $product['price'] * $product['quantity'];
        return $verify_drinks_amount;
    }

    // Developer: ShreyasM, Working Date: 9oct2024
    // For Sides Calculation
    public function sides_calculations($product)
    {
        $verify_sides_amount = $product['price'] * $product['quantity'];
        return $verify_sides_amount;
    }

    // Developer: ShreyasM, Working Date: 10oct2024
    public function custom_pizza_calculations($product)
    {
        $calculatedPrice = 0;
        $pizzaSizePrice = $product['pizzaPrice'];
        $totalTwoToppings = 0;
        $totalOneToppings = 0;
        $totalFreeToppings = 0;
        $totalDrinks = 0;
        $totalDips = 0;
        $totalSides = 0;

        // Pizza configuration
        $pizzaConfig = $product['config']['pizza'][0];

        // Toppings calculations
        if (isset($pizzaConfig['toppings']['countAsTwoToppings'])) {
            foreach ($pizzaConfig['toppings']['countAsTwoToppings'] as $twoTps) {
                $totalTwoToppings += (float)$twoTps['amount'];
            }
        }

        if (isset($pizzaConfig['toppings']['countAsOneToppings'])) {
            foreach ($pizzaConfig['toppings']['countAsOneToppings'] as $oneTps) {
                $totalOneToppings += (float)$oneTps['amount'];
            }
        }

        if (isset($pizzaConfig['toppings']['freeToppings'])) {
            foreach ($pizzaConfig['toppings']['freeToppings'] as $freeTps) {
                $totalFreeToppings += (float)$freeTps['amount'];
            }
        }

        // Sides, dips, and drinks
        if (isset($product['config']['drinks'])) {
            foreach ($product['config']['drinks'] as $drinks) {
                $totalDrinks += (float)$drinks['drinksPrice'] * (int)$drinks['quantity'];
            }
        }

        if (isset($product['config']['dips'])) {
            foreach ($product['config']['dips'] as $dips) {
                $totalDips += (float)$dips['dipsPrice'] * (int)$dips['quantity'];
            }
        }

        if (isset($product['config']['sides'])) {
            foreach ($product['config']['sides'] as $side) {
                $totalSides += (float)$side['sidePrice'] * (int)$side['quantity'];
            }
        }

        // Add base prices and toppings to the calculated price
        $calculatedPrice += (float)$pizzaSizePrice;
        $calculatedPrice += isset($pizzaConfig['crust']['price']) ? (float)$pizzaConfig['crust']['price'] : 0;
        $calculatedPrice += isset($pizzaConfig['crustType']['price']) ? (float)$pizzaConfig['crustType']['price'] : 0;
        $calculatedPrice += isset($pizzaConfig['cheese']['price']) ? (float)$pizzaConfig['cheese']['price'] : 0;
        $calculatedPrice += isset($pizzaConfig['specialBases']['price']) ? (float)$pizzaConfig['specialBases']['price'] : 0;
        $calculatedPrice += $totalTwoToppings;
        $calculatedPrice += $totalOneToppings;
        $calculatedPrice += $totalFreeToppings;
        $calculatedPrice += $totalDrinks;
        $calculatedPrice += $totalDips;
        $calculatedPrice += $totalSides;

        $verify_custompizza_amount = $calculatedPrice;
        return $verify_custompizza_amount;
    }

    // Dveloper: ShreyasM, Working Date: 10oct2024
    public function special_pizza_calculations($product, $nonRegularToppingCount)
    {

        $calculatedPrice = 0;
        // $totalOneTpsPrice = 0;
        // $totalTwoTpsPrice = 0;

        // $calcOneTpsArr = [];
        // $calcTwoTpsArr = [];
        // $noOfAdditionalTps = 0;
        // $noOfFreeToppings = 0;

        $calculatedPrice += (float)$product['pizzaPrice'];

        // foreach ($product['config']['pizza'] as $item) {
        //     if (isset($item['crust']['price'])) {
        //         $calculatedPrice += (float)$item['crust']['price'];
        //     }
        //     if (isset($item['crustType']['price'])) {
        //         $calculatedPrice += (float)$item['crustType']['price'];
        //     }
        //     if (isset($item['cheese']['price'])) {
        //         $calculatedPrice += (float)$item['cheese']['price'];
        //     }
        //     if (isset($item['specialBases']['price'])) {
        //         $calculatedPrice += (float)$item['specialBases']['price'];
        //     }
        //     if (isset($item['spicy']['price'])) {
        //         $calculatedPrice += (float)$item['spicy']['price'];
        //     }
        //     if (isset($item['sauce']['price'])) {
        //         $calculatedPrice += (float)$item['sauce']['price'];
        //     }
        //     if (isset($item['cook']['price'])) {
        //         $calculatedPrice += (float)$item['cook']['price'];
        //     }
        // }

        $getSpecialData = DB::table('specialoffer')->where('code', $product['productCode'])->select('*')->first();
        // for ($i = 0; $i < $getSpecialData->noofPizza; $i++) {
        //     foreach ($product['config']['pizza'] as $pizzaIndex => $data) {
        //         // Handle CountAsOne Toppings
        //         if (!empty($data['toppings']['countAsOneToppings'])) {
        //             foreach ($data['toppings']['countAsOneToppings'] as $key => $item) {
        //                 if ($pizzaIndex === $i) {
        //                     if ($noOfFreeToppings > 0) {
        //                         $tpsObj = [
        //                             ...$item,
        //                             'amount' => 0,
        //                         ];
        //                         $calcOneTpsArr[] = $tpsObj;
        //                         $noOfFreeToppings--;
        //                     } else {
        //                         $calcOneTpsArr[] = $item;
        //                         $noOfAdditionalTps++;
        //                     }
        //                 }
        //             }
        //         }

        //         // Handle CountAsTwo Toppings
        //         if (!empty($data['toppings']['countAsTwoToppings'])) {
        //             foreach ($data['toppings']['countAsTwoToppings'] as $item) {
        //                 if ($pizzaIndex === $i) {
        //                     if ($noOfFreeToppings > 1) {
        //                         $tpsObj = [
        //                             ...$item,
        //                             'amount' => 0,
        //                         ];
        //                         $calcTwoTpsArr[] = $tpsObj;
        //                         $noOfFreeToppings -= 2;
        //                     } elseif ($noOfFreeToppings === 1) {
        //                         $tpsObj = [
        //                             ...$item,
        //                             'amount' => $nonRegularToppingCount >= 2 ? (float) $item['toppingsPrice'] / $nonRegularToppingCount : 0,
        //                         ];
        //                         $calcTwoTpsArr[] = $tpsObj;
        //                         $noOfFreeToppings--;
        //                         if ($nonRegularToppingCount) {
        //                             $noOfAdditionalTps++;
        //                         }
        //                     } else {
        //                         $calcTwoTpsArr[] = $item;
        //                         $noOfAdditionalTps += 2;
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }

        if ($product['config']['dips'] > 0) {
            $calcDipsArr = [];
            $totalDipsPrice = 0;
            $noOfFreeDips = $getSpecialData->noofDips;
            $noOfAdditionalDips = 0;
            foreach ($product['config']['dips'] as $item) {
                $usedFreeDips = 0;

                if ($noOfFreeDips > 0) {
                    if ($item['quantity'] <= $noOfFreeDips) {
                        $usedFreeDips = $item['quantity'];
                    } else {
                        $usedFreeDips = $noOfFreeDips;
                    }

                    $noOfFreeDips -= $usedFreeDips;
                }

                $paidQuantity = (int)$item['quantity'] - (int)$usedFreeDips;
                $noOfAdditionalDips += $paidQuantity;

                $dipsObj = [
                    'quantity' => $item['quantity'],
                    'dipsPrice' => $item['dipsPrice'],
                    'freeQuantity' => $usedFreeDips,
                    'paidQuantity' => $paidQuantity,
                    'totalPrice' => (float)$paidQuantity * (float)$item['dipsPrice'],
                ];

                $calcDipsArr[] = $dipsObj;
            }
        }

        foreach ($calcDipsArr as $dips) {
            $totalDipsPrice += (float)($dips['totalPrice'] ?? 0);
        }

        $calculatedPrice += $totalDipsPrice;

        // // Calculate total prices for CountAsOne Toppings
        // foreach ($calcOneTpsArr as $tps) {
        //     $totalOneTpsPrice += (float) $tps['amount'];
        // }
        // $calculatedPrice += $totalOneTpsPrice;

        // // Calculate total prices for CountAsTwo Toppings
        // foreach ($calcTwoTpsArr as $tps) {
        //     $totalTwoTpsPrice += (float) $tps['amount'];
        // }
        // $calculatedPrice += $totalTwoTpsPrice;

        $verify_specialpizza_amount = $calculatedPrice * $product['quantity'];
        return $verify_specialpizza_amount;
    }

    /**
     * Summary of signature_pizza_calculations
     * @param mixed $product
     * @return float
     */
    public function signature_pizza_calculations($product)
    {
        $calculatedPrice = 0;
        $calculatedPrice += (float)$product['pizzaPrice'];
        $pizzaConfig = $product['config']['pizza'][0];


        if (isset($pizzaConfig['crust']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['crust']['price'];
        }
        if (isset($pizzaConfig['crustType']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['crustType']['price'];
        }
        if (isset($pizzaConfig['cheese']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['cheese']['price'];
        }
        if (isset($pizzaConfig['specialBases']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['specialBases']['price'];
        }
        if (isset($pizzaConfig['spicy']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['spicy']['price'];
        }
        if (isset($pizzaConfig['sauce']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['sauce']['price'];
        }
        if (isset($pizzaConfig['cook']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['cook']['price'];
        }
        if (isset($pizzaConfig['toppings']['countAsOneToppings'])) {
            foreach ($pizzaConfig['toppings']['countAsOneToppings'] as $oneTps) {
                $calculatedPrice += (float)$oneTps['amount'];
            }
        }
        if (isset($pizzaConfig['toppings']['countAsTwoToppings'])) {
            foreach ($pizzaConfig['toppings']['countAsTwoToppings'] as $twoTps) {
                $calculatedPrice += (float)$twoTps['amount'];
            }
        }
        if (isset($pizzaConfig['toppings']['freeToppings'])) {
            foreach ($pizzaConfig['toppings']['freeToppings'] as $freeTps) {
                $calculatedPrice += (float)$freeTps['amount'];
            }
        }

        $verify_specialpizza_amount = $calculatedPrice * (int)$product['quantity'];
        return $verify_specialpizza_amount;
    }

    /**
     * Summary of signature_pizza_calculations
     * @param mixed $product
     * @return float
     */
    public function other_pizza_calculations($product)
    {
        $calculatedPrice = 0;
        $calculatedPrice += (float)$product['pizzaPrice'];
        $pizzaConfig = $product['config']['pizza'][0];

        if (isset($pizzaConfig['crust']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['crust']['price'];
        }
        if (isset($pizzaConfig['crustType']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['crustType']['price'];
        }
        if (isset($pizzaConfig['cheese']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['cheese']['price'];
        }
        if (isset($pizzaConfig['specialBases']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['specialBases']['price'];
        }
        if (isset($pizzaConfig['spicy']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['spicy']['price'];
        }
        if (isset($pizzaConfig['sauce']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['sauce']['price'];
        }
        if (isset($pizzaConfig['cook']['price'])) {
            $calculatedPrice += (float)$pizzaConfig['cook']['price'];
        }
        if (isset($pizzaConfig['toppings']['countAsOneToppings'])) {
            foreach ($pizzaConfig['toppings']['countAsOneToppings'] as $oneTps) {
                $calculatedPrice += (float)$oneTps['amount'];
            }
        }
        if (isset($pizzaConfig['toppings']['countAsTwoToppings'])) {
            foreach ($pizzaConfig['toppings']['countAsTwoToppings'] as $twoTps) {
                $calculatedPrice += (float)$twoTps['amount'];
            }
        }
        if (isset($pizzaConfig['toppings']['freeToppings'])) {
            foreach ($pizzaConfig['toppings']['freeToppings'] as $freeTps) {
                $calculatedPrice += (float)$freeTps['amount'];
            }
        }

        $verify_specialpizza_amount = $calculatedPrice * (int)$product['quantity'];
        return $verify_specialpizza_amount;
    }


    // Developer: ShreyasM, Working Date: 9oct2024
    // For GrandTotal Calculation
    public function grand_total_calculations($verify_sub_total, $cart)
    {
        $discountAmount = 0.0;
        $deliveryCharges = 0.0;
        $extraDeliveryCharges = 0.0;
        $storeCode = $cart['storeCode'];
        $deliveryType = $cart['deliveryType'];
        if (isset($cart['discountAmount'])) {
            $discountAmount = $cart['discountAmount'];
        }
        if ($deliveryType != "pickup") {
            $delvSettings = Setting::where('code', 'STG_1')->where('isActive', 1)->first();
            if ($delvSettings) {
                $deliveryCharges = $delvSettings->settingValue;
            }
            /*
            if (isset($cart['deliveryCharges'])) {
                $deliveryCharges = $cart['deliveryCharges'];
            }
            if (isset($cart['extraDeliveryCharges'])) {
                $extraDeliveryCharges = $cart['extraDeliveryCharges'];
            }
            */
        }
        // $taxPercentage = Setting::where('code', 'STG_2')->first()->settingValue;

        $provinceId =  Storelocation::where('code', $storeCode)->first()->tax_province_id ?? null;
        // dd($provinceId, $cart['storeCode']);
        if ($provinceId) {
            $taxPercentage = Province::where('id', $provinceId)->first()->tax_percent;
        }
        $convinenceCharges = Setting::where('code', 'STG_4')->first()->settingValue;

        $taxAmount = ($verify_sub_total * $taxPercentage) / 100;
        $discountedAmount = $verify_sub_total - $discountAmount;
        $convinenceAmount = ($verify_sub_total * $convinenceCharges) / 100;
        $taxableTotal = $taxAmount + $convinenceAmount;
        $taxableTotalAmount = $discountedAmount + $taxableTotal;

        $gTotal = $taxableTotalAmount + $deliveryCharges + $extraDeliveryCharges;

        $data = [
            'subTotal' => number_format($verify_sub_total, 2),
            'discountAmount' => number_format($discountAmount, 2),
            'taxPer' => number_format($taxPercentage, 2),
            'taxAmount' => number_format($taxAmount, 2),
            'convinenceCharges' => number_format($convinenceCharges, 2),
            'deliveryCharges' => number_format($deliveryCharges, 2),
            'extraDeliveryCharges' => number_format($extraDeliveryCharges, 2),
            //'grandTotal' => floor($gTotal * 100) / 100,
            "grandTotal" => number_format($gTotal, 2),
        ];
        return $data;
    }
}
