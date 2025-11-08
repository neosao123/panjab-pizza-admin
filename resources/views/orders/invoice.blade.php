<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Invoice</title>

    <style>
        html,
        body {
            margin: 10px;
            padding: 10px;
            font-family: sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        span,
        label {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 10px;
            margin-bottom: 0px !important;

        }

        table thead th {
            height: 10px;
            text-align: left;
            font-size: 16px;
            font-family: sans-serif;
        }

        td {
            padding: 5px;

        }

        .table-border-style {
            border: 1px solid #787878;
            padding: 2px;
            font-size: 14px;
            vertical-align: top;
        }

        .table-border-header {
            border: 1px solid #787878;
            padding: 2px;
            font-size: 16px;
        }

        .logo {
            padding: 8px;
            font-size: 10px;
        }

        .heading {
            font-size: 24px;
            margin-top: 12px;
            margin-bottom: 12px;
            font-family: sans-serif;
        }

        .small-heading {
            font-size: 18px;
            font-family: sans-serif;
        }

        .total-heading {
            font-size: 8px;
            font-family: sans-serif;
        }

        .order-details tbody tr td:nth-child(1) {
            width: 20%;
        }

        .order-details tbody tr td:nth-child(3) {
            width: 20%;
        }

        .text-start {
            text-align: left;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .company-data span {
            margin-bottom: 4px;
            display: inline-block;
            font-family: sans-serif;
            font-size: 14px;

        }

        .no-border {
            border: 1px solid #fff !important;
        }

        .bg-blue {
            background-color: #01508e;
            color: #fff;
        }
    </style>
</head>

<body>
    @if ($orders)
        @php
            $orderCount = 0;
            if ($orderlineentries && count($orderlineentries) > 0) {
                $orderCount = count($orderlineentries);
            }
            $allowedPizzas = ['custom_pizza', 'signature_pizza', 'other_pizza'];
            $allowedSpecialPizzas = ['special_pizza'];
        @endphp
        <table width="100%" class="table-border-style">
            <thead>
                <tr>
                    <td colspan="4" style="text-align:center"><img src="{{ asset('uploads/mr-singhs-pizza-logo.png') }}"
                            height="80" width="80"></td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align:center">
                        <h2>Mr. Singh's Pizza</h2>
                        <span>{{ $orders->storeAddress }}</span><br>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">Date: {{ date('d-m-Y', strtotime($orders->created_at)) }} Time:
                        {{ date('h:i A', strtotime($orders->created_at)) }}</td>
                    <td colspan="2" style="text-align:right">Order No:{{ $orders->orderCode }}</td>

                </tr>
                <tr>
                    @if ($orders->deliveryType == 'delivery')
                        <td colspan="4">Name:
                            {{ $orders->customerName }},{{ $orders->mobileNumber }},{{ $orders->address }}</td>
                    @endif
                    @if ($orders->deliveryType == 'pickup')
                        <td colspan="4">Name:{{ $orders->mobileNumber }}</td>
                    @endif
                </tr>
                <tr>
                    @if ($orders->orderTakenBy != '')
                        <td colspan="4">OTB:{{ $orders->orderTakenBy }}</td>
                    @else
                        <td colspan="4">OTB:{{ $orders->customerName }}</td>
                    @endif
                </tr>
                <tr>
                    <th class="table-border-style" style="width:5%;">Sr/no</th>
                    <th class="table-border-style" style="width:10%">Qty</th>
                    <th class="table-border-style" style="width:60%">Product</th>
                    <th class="table-border-style" style="width:10%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if ($orderCount > 0)
                    @for ($i = 0; $i < $orderCount; $i++)
                        @php
                            $data = json_decode($orderlineentries[$i]->config, true);
                        @endphp
                        <tr>
                            <td class="table-border-style">
                                {{ $i + 1 }}
                            </td>
                            <td class="table-border-style">
                                {{ $orderlineentries[$i]->quantity }}
                                @if ($orderlineentries[$i]->productType == 'side' && isset($data['sidesSize']))
                                    <span>&nbsp; </span><br>
                                @endif
                                @if (
                                    $orderlineentries[$i]->productType == 'side' ||
                                        $orderlineentries[$i]->productType == 'dips' ||
                                        $orderlineentries[$i]->productType == 'drinks')
                                    <span>&nbsp; </span><br>
                                @endif
                                @if ($orderlineentries[$i]->productType == 'custom_pizza' || $orderlineentries[$i]->productType == 'special_pizza')
                                    @if (isset($data['pizza']))
                                        @foreach ($data['pizza'] as $pizzaItem)
                                            @if (isset($pizzaItem['crust']) && $pizzaItem['crust']['crustName'] != 'Regular')
                                                <span>&nbsp; </span><br>
                                            @endif
                                            @if (isset($pizzaItem['crustType']) && $pizzaItem['crustType']['crustType'] != 'Regular')
                                                <span>&nbsp; </span><br>
                                            @endif
                                            @if (isset($pizzaItem['cheese']) && $pizzaItem['cheese']['cheeseName'] != 'Mozzarella')
                                                <span>&nbsp; </span><br>
                                            @endif
                                            @if (isset($pizzaItem['specialBases']) && isset($pizzaItem['specialBases']['specialbaseName']))
                                                <span>&nbsp; </span><br>
                                            @endif
                                            @if (isset($pizzaItem['spicy']) && $pizzaItem['spicy']['spicy'] != 'Regular')
                                                <span>&nbsp; </span><br>
                                            @endif
                                            @if (isset($pizzaItem['sauce']) && $pizzaItem['sauce']['sauce'] != 'Regular')
                                                <span>&nbsp; </span><br>
                                            @endif
                                            @if (isset($pizzaItem['cook']) && $pizzaItem['cook']['cook'] != 'Regular')
                                                <span>&nbsp; </span><br>
                                            @endif
                                            @if (isset($pizzaItem['toppings']['countAsTwoToppings']) && count($pizzaItem['toppings']['countAsTwoToppings']) > 0)
                                                <span>&nbsp; </span><br>
                                                @foreach ($pizzaItem['toppings']['countAsTwoToppings'] as $topping)
                                                    <span>&nbsp; </span><br>
                                                @endforeach
                                                @foreach ($pizzaItem['toppings']['countAsOneToppings'] as $topping)
                                                    <span>&nbsp; </span><br>
                                                @endforeach
                                                @if ($pizzaItem['toppings']['isAllIndiansTps'] == true)
                                                    <span>&nbsp; </span><br>
                                                @else
                                                    @foreach ($pizzaItem['toppings']['freeToppings'] as $topping)
                                                        <span>&nbsp; </span><br>
                                                    @endforeach
                                                @endif
                                            @endif
                                        @endforeach
                                    @endif
                                    @if (isset($data['sides']) && count($data['sides']) > 0)
                                        <span>&nbsp;</span><br>
                                        @foreach ($data['sides'] as $sides)
                                            <span style="font-weight: normal;">{{ $sides['quantity'] }}</span><br>
                                        @endforeach
                                    @endif
                                    @if (isset($data['dips']) && count($data['dips']) > 0)
                                        <span>&nbsp; </span><br>
                                        @foreach ($data['dips'] as $dips)
                                            <span>&nbsp;</span><br>
                                        @endforeach
                                    @endif
                                    @if (isset($data['drinks']) && count($data['drinks']) > 0)
                                        <span>&nbsp; </span><br>
                                        @foreach ($data['drinks'] as $drinks)
                                            <span>&nbsp; </span><br>
                                        @endforeach
                                    @endif
                                    @if (isset($orderlineentries[$i]->comments))
                                        @if ($orderlineentries[$i]->comments != '')
                                            <span>&nbsp; </span><br>
                                        @endif
                                    @endif
                                @endif
                            </td>
                            <td class="text-start table-border-style">
                                {{ $orderlineentries[$i]->productName }}<br>
                                @if ($orderlineentries[$i]->productType == 'side' && isset($data['sidesSize']))
                                    <strong>Sides: </strong><span
                                        style="font-weight: normal;">{{ $data['sidesSize'] }}</span><br>
                                @endif
                                @if (
                                    $orderlineentries[$i]->productType == 'side' ||
                                        $orderlineentries[$i]->productType == 'dips' ||
                                        $orderlineentries[$i]->productType == 'drinks')
                                    @if ($orderlineentries[$i]->comments != '')
                                        <strong>Comments: </strong><span
                                            style="font-weight: normal;">{{ $orderlineentries[$i]->comments }}</span><br>
                                    @endif
                                @endif

                                @if (in_array($orderlineentries[$i]->productType, $allowedPizzas))
                                    @if (isset($data['pizza']))
                                        @foreach ($data['pizza'] as $pizzaItem)
                                            @if (isset($pizzaItem['crust']) && $pizzaItem['crust']['crustName'] != 'Regular')
                                                <strong>Crust: </strong><span
                                                    style="font-weight: normal;">{{ $pizzaItem['crust']['crustName'] }}</span><br>
                                            @endif
                                            @if (isset($pizzaItem['crustType']) && $pizzaItem['crustType']['crustType'] != 'Regular')
                                                <strong>Crust Type: </strong><span
                                                    style="font-weight: normal;">{{ $pizzaItem['crustType']['crustType'] }}</span><br>
                                            @endif
                                            @if (isset($pizzaItem['cheese']) && $pizzaItem['cheese']['cheeseName'] != 'Mozzarella')
                                                <strong>Cheese: </strong><span
                                                    style="font-weight: normal;">{{ $pizzaItem['cheese']['cheeseName'] }}</span><br>
                                            @endif
                                            @if (isset($pizzaItem['specialBases']) && isset($pizzaItem['specialBases']['specialbaseName']))
                                                @if (isset($pizzaItem['specialBases']['specialbaseName']))
                                                    <strong>SpecialBases: </strong><span
                                                        style="font-weight: normal;">{{ $pizzaItem['specialBases']['specialbaseName'] }}</span><br>
                                                @endif
                                            @endif
                                            @if (isset($pizzaItem['spicy']) && $pizzaItem['spicy']['spicy'] != 'Regular')
                                                <strong>Spicy: </strong><span
                                                    style="font-weight: normal;">{{ $pizzaItem['spicy']['spicy'] }}</span><br>
                                            @endif
                                            @if (isset($pizzaItem['sauce']) && $pizzaItem['sauce']['sauce'] != 'Regular')
                                                <strong>Sauce: </strong><span
                                                    style="font-weight: normal;">{{ $pizzaItem['sauce']['sauce'] }}</span><br>
                                            @endif
                                            @if (isset($pizzaItem['cook']) && $pizzaItem['cook']['cook'] != 'Regular')
                                                <strong>Cook: </strong><span
                                                    style="font-weight: normal;">{{ $pizzaItem['cook']['cook'] }}</span><br>
                                            @endif
                                            @if (isset($pizzaItem['toppings']['countAsTwoToppings']) && count($pizzaItem['toppings']['countAsTwoToppings']) > 0)
                                                @foreach ($pizzaItem['toppings']['countAsTwoToppings'] as $topping)
                                                    <strong>(2) </strong><span style="font-weight: normal;">
                                                        {{ $topping['toppingsName'] }}
                                                        @if ($topping['toppingsPlacement'] == 'lefthalf')
                                                            (L)
                                                        @elseif ($topping['toppingsPlacement'] == 'righthalf')
                                                            ( R )
                                                        @elseif ($topping['toppingsPlacement'] == '1/4')
                                                            ( 1/4 )
                                                        @endif
                                                    </span><br>
                                                @endforeach
                                                @foreach ($pizzaItem['toppings']['countAsOneToppings'] as $topping)
                                                    <strong> </strong><span style="font-weight: normal;">
                                                        {{ $topping['toppingsName'] }}
                                                        @if ($topping['toppingsPlacement'] == 'lefthalf')
                                                            (L)
                                                        @elseif ($topping['toppingsPlacement'] == 'righthalf')
                                                            ( R )
                                                        @elseif ($topping['toppingsPlacement'] == '1/4')
                                                            ( 1/4 )
                                                        @endif
                                                    </span><br>
                                                @endforeach
                                                @if ($pizzaItem['toppings']['isAllIndiansTps'] == true)
                                                    <strong>Indian Style<strong /><br>
                                                    @else
                                                        @foreach ($pizzaItem['toppings']['freeToppings'] as $topping)
                                                            <strong></strong><span
                                                                style="font-weight: normal;">{{ $topping['toppingsName'] }}</span><br>
                                                        @endforeach
                                                @endif
                                            @endif
                                        @endforeach
                                    @endif
                                    @if (isset($data['sides']) && count($data['sides']) > 0)
                                        <span><strong>Sides: </strong></span><br>
                                        @foreach ($data['sides'] as $sides)
                                            <span style="font-weight: normal;">{{ $sides['sideName'] }} (
                                                {{ $sides['sideSize'] }} )</span><br>
                                        @endforeach
                                    @endif
                                    @if (isset($data['dips']) && count($data['dips']) > 0)
                                        <span><strong>Dips: </strong></span><br>
                                        @foreach ($data['dips'] as $dips)
                                            <span style="font-weight: normal;">{{ $dips['dipsName'] }}</span><br>
                                        @endforeach
                                    @endif
                                    @if (isset($data['drinks']) && count($data['drinks']) > 0)
                                        <span><strong>Drinks: </strong></span><br>
                                        @foreach ($data['drinks'] as $drinks)
                                            <span style="font-weight: normal;">{{ $drinks['drinksName'] }}</span><br>
                                        @endforeach
                                    @endif
                                    @if (isset($orderlineentries[$i]->comments))
                                        @if ($orderlineentries[$i]->comments != '')
                                            <strong>Comments: </strong><span
                                                style="font-weight: normal;">{{ $orderlineentries[$i]->comments }}</span><br>
                                        @endif
                                    @endif
                                @endif

                                @if (in_array($orderlineentries[$i]->productType, $allowedSpecialPizzas))
                                    @if (isset($data['pizza']))
                                        @foreach ($data['pizza'] as $pizzaItem)
                                            @if (isset($pizzaItem['toppings']['countAsTwoToppings']) && count($pizzaItem['toppings']['countAsTwoToppings']) > 0)
                                                @foreach ($pizzaItem['toppings']['countAsTwoToppings'] as $topping)
                                                    <strong>(2) </strong><span style="font-weight: normal;">
                                                        {{ $topping['toppingsName'] }}
                                                        @if ($topping['toppingsPlacement'] == 'lefthalf')
                                                            (L)
                                                        @elseif ($topping['toppingsPlacement'] == 'righthalf')
                                                            ( R )
                                                        @elseif ($topping['toppingsPlacement'] == '1/4')
                                                            ( 1/4 )
                                                        @endif
                                                    </span><br>
                                                @endforeach
                                                @foreach ($pizzaItem['toppings']['countAsOneToppings'] as $topping)
                                                    <strong> </strong><span style="font-weight: normal;">
                                                        {{ $topping['toppingsName'] }}
                                                        @if ($topping['toppingsPlacement'] == 'lefthalf')
                                                            (L)
                                                        @elseif ($topping['toppingsPlacement'] == 'righthalf')
                                                            ( R )
                                                        @elseif ($topping['toppingsPlacement'] == '1/4')
                                                            ( 1/4 )
                                                        @endif
                                                    </span><br>
                                                @endforeach
                                                @if ($pizzaItem['toppings']['isAllIndiansTps'] == true)
                                                    <strong>Indian Style<strong /><br>
                                                    @else
                                                        @foreach ($pizzaItem['toppings']['freeToppings'] as $topping)
                                                            <strong></strong><span
                                                                style="font-weight: normal;">{{ $topping['toppingsName'] }}</span><br>
                                                        @endforeach
                                                @endif
                                            @endif
                                        @endforeach
                                    @endif
                                    @if (isset($data['sides']) && count($data['sides']) > 0)
                                        <span><strong>Sides: </strong></span><br>
                                        @foreach ($data['sides'] as $sides)
                                            <span style="font-weight: normal;">{{ $sides['sideName'] }} (
                                                {{ $sides['sideSize'] }} )</span><br>
                                        @endforeach
                                    @endif
                                    @if (isset($data['dips']) && count($data['dips']) > 0)
                                        <span><strong>Dips: </strong></span><br>
                                        @foreach ($data['dips'] as $dips)
                                            <span style="font-weight: normal;">{{ $dips['dipsName'] }}</span><br>
                                        @endforeach
                                    @endif
                                    @if (isset($data['drinks']) && count($data['drinks']) > 0)
                                        <span><strong>Drinks: </strong></span><br>
                                        @foreach ($data['drinks'] as $drinks)
                                            <span style="font-weight: normal;">{{ $drinks['drinksName'] }}</span><br>
                                        @endforeach
                                    @endif
                                    @if (isset($orderlineentries[$i]->comments))
                                        @if ($orderlineentries[$i]->comments != '')
                                            <strong>Comments: </strong><span
                                                style="font-weight: normal;">{{ $orderlineentries[$i]->comments }}</span><br>
                                        @endif
                                    @endif
                                @endif


                            </td>
                            <td class="text-start table-border-style">
                                @if ($orderlineentries[$i]->productType == 'custom_pizza' || $orderlineentries[$i]->productType == 'special_pizza')
                                    <span>$ {{ $orderlineentries[$i]->pizzaPrice }}</span><br>
                                @else
                                    <span>$ {{ $orderlineentries[$i]->amount }}</span><br>
                                @endif

                                @if ($orderlineentries[$i]->productType == 'side' && isset($data['sidesSize']))
                                    <span>&nbsp;</span><br>
                                @endif
                                @if (
                                    $orderlineentries[$i]->productType == 'side' ||
                                        $orderlineentries[$i]->productType == 'dips' ||
                                        $orderlineentries[$i]->productType == 'drinks')
                                    @if ($orderlineentries[$i]->comments != '')
                                        <span>&nbsp;</span><br>
                                    @endif
                                @endif
                                @if ($orderlineentries[$i]->productType == 'custom_pizza' || $orderlineentries[$i]->productType == 'special_pizza')
                                    @if (isset($data['pizza']))
                                        @foreach ($data['pizza'] as $pizzaItem)
                                            @if (isset($pizzaItem['crust']) && $pizzaItem['crust']['crustName'] != 'Regular')
                                                <span>
                                                    @if ($pizzaItem['crust']['price'] != 0)
                                                        $ {{ $pizzaItem['crust']['price'] }}
                                                    @endif
                                                </span><br>
                                            @endif
                                            @if (isset($pizzaItem['crustType']) && $pizzaItem['crustType']['crustType'] != 'Regular')
                                                <span>
                                                    @if ($pizzaItem['crustType']['price'] != 0)
                                                        $ {{ $pizzaItem['crustType']['price'] }}
                                                    @endif
                                                </span><br>
                                            @endif
                                            @if (isset($pizzaItem['cheese']) && $pizzaItem['cheese']['cheeseName'] != 'Mozzarella')
                                                <span>
                                                    @if ($pizzaItem['cheese']['price'] != 0)
                                                        $ {{ $pizzaItem['cheese']['price'] }}
                                                    @endif
                                                </span><br>
                                            @endif
                                            @if (isset($pizzaItem['specialBases']) && isset($pizzaItem['specialBases']['specialbaseName']))
                                                <span>
                                                    @if ($pizzaItem['specialBases']['price'] != 0)
                                                        $ {{ $pizzaItem['specialBases']['price'] }}
                                                    @endif
                                                </span><br>
                                            @endif
                                            @if (isset($pizzaItem['spicy']) && $pizzaItem['spicy']['spicy'] != 'Regular')
                                                <span>
                                                    @if ($pizzaItem['spicy']['price'] != 0)
                                                        $ {{ $pizzaItem['spicy']['price'] }}
                                                    @endif
                                                </span><br>
                                            @endif
                                            @if (isset($pizzaItem['sauce']) && $pizzaItem['sauce']['sauce'] != 'Regular')
                                                <span>
                                                    @if ($pizzaItem['sauce']['price'] != 0)
                                                        $ {{ $pizzaItem['sauce']['price'] }}
                                                    @endif
                                                </span><br>
                                            @endif
                                            @if (isset($pizzaItem['cook']) && $pizzaItem['cook']['cook'] != 'Regular')
                                                <span>
                                                    @if ($pizzaItem['cook']['price'] != 0)
                                                        $ {{ $pizzaItem['cook']['price'] }}
                                                    @endif
                                                </span><br>
                                            @endif
                                            @if (isset($pizzaItem['toppings']['countAsTwoToppings']) && count($pizzaItem['toppings']['countAsTwoToppings']) > 0)
                                                <span>&nbsp;</span><br>
                                                @foreach ($pizzaItem['toppings']['countAsTwoToppings'] as $topping)
                                                    <span>
                                                        @if (isset($topping['amount']) && $topping['amount'] != '0')
                                                            $ {{ $topping['amount'] }}
                                                        @endif
                                                    </span><br>
                                                @endforeach
                                                @foreach ($pizzaItem['toppings']['countAsOneToppings'] as $topping)
                                                    <span>
                                                        @if (isset($topping['amount']) && $topping['amount'] != '0')
                                                            $ {{ $topping['amount'] }}
                                                        @endif
                                                    </span><br>
                                                @endforeach
                                                @if ($pizzaItem['toppings']['isAllIndiansTps'] == true)
                                                    <span>&nbsp;</span><br>
                                                @else
                                                    @foreach ($pizzaItem['toppings']['freeToppings'] as $topping)
                                                        <span>&nbsp;</span><br>
                                                    @endforeach
                                                @endif
                                            @endif
                                        @endforeach
                                    @endif
                                    @if (isset($data['sides']) && count($data['sides']) > 0)
                                        <span>&nbsp;</span><br>
                                        @foreach ($data['sides'] as $sides)
                                            <span
                                                style="font-weight: normal;">{{ "$" }}{{ $sides['totalPrice'] }}</span><br>
                                        @endforeach
                                    @endif
                                    @if (isset($data['dips']) && count($data['dips']) > 0)
                                        @foreach ($data['dips'] as $dips)
                                            <span>$ {{ $dips['totalPrice'] }}</span><br>
                                        @endforeach
                                    @endif
                                    @if (isset($data['drinks']) && count($data['drinks']) > 0)
                                        @foreach ($data['drinks'] as $drinks)
                                            <span>$ {{ $drinks['totalPrice'] }}</span><br>
                                        @endforeach
                                    @endif
                                    @if (isset($orderlineentries[$i]->comments))
                                        @if ($orderlineentries[$i]->comments != '')
                                            <span>&nbsp;</span><br>
                                        @endif
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endfor
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right;"><b>Sub Total :</b></td>
                    <td colspan="1" style="text-align:right;">$ {{ $orders->subTotal }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align:right;"><b> Discount :</b></td>
                    <td colspan="1" style="text-align:right;">$ {{ $orders->discountAmount }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align:right;"><b>Tax Amount: </b></td>
                    <td colspan="1" style="text-align:right;">$ {{ $orders->taxAmount }}</td>
                </tr>
                @if ($orders->deliveryType != 'pickup')
                    <tr>
                        <td colspan="3" style="text-align:right;"><b>Delivery Charges :</b></td>
                        <td colspan="1" style="text-align:right;">$ {{ $orders->deliveryCharges }}</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="3" style="text-align:right;"><b>Grand Total :</b></td>
                    <td colspan="1" style="text-align:right;">$ {{ $orders->grandTotal }}</td>
                </tr>
            </tfoot>
        </table>
        <table style="display:none;">
            <thead>
                <tr>
                    <td colspan="1">
                        <img src="{{ asset('uploads/qr.png') }}" height="120" width="120">
                    </td>
                    <td colspan="3">
                        Don't forget to rate us !<br>
                        Sacn the QR Code to let us know how you enjoyed our pizza. <br><br><br>
                        100% Vegetarian<br>
                        We hope see you soon !<br>
                    </td>
                </tr>
            </thead>
        </table>
    @endif
</body>

</html>
