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
	@if($orders)
	@php
	$orderCount = 0;
	if($orderlineentries && count($orderlineentries)>0) $orderCount = count($orderlineentries);
	@endphp
	<table width="100%" class="table-border-style">
		<thead>
			<tr>
				<td colspan="5" style="text-align:center"><img src="{{ asset('uploads/mr-singhs-pizza-logo.png') }}" height="80" width="80"></td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:center">
					<h2>Mr. Singh's Pizza</h2>
					<span>2120 N Park Dr Unit #25, Brampton, ON L6S 0C9</span><br>
					<span>905-500-4000</span>
				</td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:center"></td>
			</tr>
			<tr>
				<td colspan="3">Date: {{date('Y-m-d',strtotime($orders->created_at))}}</td>
				<td colspan="2">Time: {{date('h:i:s',strtotime($orders->created_at))}}</td>
			</tr>
			<tr>
				<td colspan="5">Name: {{$orders->customerName}},{{$orders->mobileNumber}},{{$orders->address}}</td>
			</tr>
			<tr>
				<th class="table-border-style" style="width:5%">Sr/no</th>
				<th class="table-border-style" style="width:10%">Qty</th>
				<th class="table-border-style" style="width:60%">Product</th>
				<th class="table-border-style" style="width:10%">Price</th>
				<th class="table-border-style" style="width:15%">Amount</th>
			</tr>
		</thead>
		<tbody>
			@if($orderCount>0)
			@for ($i = 0; $i < $orderCount; $i++) 
				@php $data=json_decode($orderlineentries[$i]->config,true);
				@endphp
				<tr>
					<td class="table-border-style">{{$i+1}}</td>
					<td class="table-border-style">{{$orderlineentries[$i]->quantity}}</td>
					<td class="text-start table-border-style">
					{{$orderlineentries[$i]->productName}}<br>
					<?php
					if ($orderlineentries[$i]->productType == "side" && isset($data['sidesSize'])) {
						   echo "<span> <b>Side: </b>" .$data['sidesSize'] . " </span><br>\n";
					}
					if ($orderlineentries[$i]->productType == "side" || $orderlineentries[$i]->productType == "dips" || $orderlineentries[$i]->productType == "drinks") {
					   if($orderlineentries[$i]->comments!=""){
						   echo "<span> <b>Comments: </b>" .$orderlineentries[$i]->comments . " </span><br>\n"; 
					   }
					}
					if ($orderlineentries[$i]->productType == "custom_pizza" || $orderlineentries[$i]->productType == "special_pizza") {						
						if (isset($data['pizza'])) {
							foreach ($data['pizza'] as $pizzaItem) {
								if(isset($pizzaItem['crust']) && $pizzaItem['crust']['crustName'] != "Regular"){
								  echo "<span> <b>Crust: </b>" . $pizzaItem['crust']['crustName'] . " </span><br>\n";
								}
								if (isset($pizzaItem['crustType']) && $pizzaItem['crustType']['crustType'] != "Regular") {
								  echo "<span> <b>Crust Type: </b>" . $pizzaItem['crustType']['crustType'] . "</span><br>\n";
								}
								if (isset($pizzaItem['cheese']) && $pizzaItem['cheese']['cheeseName'] != 'Mozzarella') {
								  echo "<span> <b>Cheese: </b>" . $pizzaItem['cheese']['cheeseName'] . "</span><br>\n";
								}
								if (isset($pizzaItem['specialBases']) && isset($pizzaItem['specialBases']['specialbaseName'])) {
								   if(isset($pizzaItem['specialBases']['specialbaseName'])){ 
									   echo "<span> <b>SpecialBases: </b>" . $pizzaItem['specialBases']['specialbaseName'] . "</span><br>\n";
								   }
								}
								if (isset($pizzaItem['spicy']) && $pizzaItem['spicy']['spicy']!="Regular") {
									echo "<span> <b>Spicy: </b>" . $pizzaItem['spicy']['spicy'] . "</span><br>\n";
								}
								if (isset($pizzaItem['sauce']) && $pizzaItem['sauce']['sauce']!="Regular") {
									echo "<span> <b>Sauce: </b>" . $pizzaItem['sauce']['sauce'] . "</span><br>\n";
								}
								if (isset($pizzaItem['cook']) && $pizzaItem['cook']['cook']!="Regular") {
									echo "<span> <b>Cook: </b>" .$pizzaItem['cook']['cook'] . "</span><br>\n";
								} 
								
								if (isset($pizzaItem['toppings']['countAsTwoToppings'])) {
									echo "<span> <b>Toppings:</b></span>";
									foreach ($pizzaItem['toppings']['countAsTwoToppings'] as $topping) {
										echo "<strong>( 2 ) </strong><span style='font-weight: normal;'>" . $topping['toppingsName'] . ' ' . ($topping['toppingsPlacement'] == "lefthalf" ? " ( L ) " :
											($topping['toppingsPlacement'] == "righthalf" ? " ( R ) " :
												($topping['toppingsPlacement'] == "1/4" ? " ( 1/4 )" : ""))) . '</span><br>\n';
									}
									foreach ($pizzaItem['toppings']['countAsOneToppings'] as $topping) {
										echo "" . $topping['toppingsName'] . ",";
									}
									foreach ($pizzaItem['toppings']['freeToppings'] as $topping) {
										echo "" . $topping['toppingsName'] . ",";
									}
								}
							}
						}

						if (isset($data['sides'])) {
							foreach ($data['sides'] as $side) {
								if (isset($side['sideName'])) {
									echo "<span> <b>Side Name: </b>" . $side['sideName'] . "</span><br>";
								}
							}
						}

						if (isset($data['dips'])) {
							foreach ($data['dips'] as $dip) {
								if (isset($dip['dipsName'])) {
									echo "<span> <b>Dip Name: </b>" . $dip['dipsName'] . "</span><br>";
								}
							}
						}

						if (isset($data['drinks'])) {
							foreach ($data['drinks'] as $drink) {
								if (isset($drink['softDrinkName'])) {
									echo "<span> <b>Drink Name:</b>" . $drink['softDrinkName'] . "</span><br>";
								}
								if (isset($drink['drinksName'])) {
									echo "<span> <b>Drink Name:</b>" . $drink['drinksName'] . "</span><br>";
								}
							}
						}
						if (isset($data['sidesSize'])) {
							echo "<span> <b>Sides Size: </b>" . $data['sidesSize'] . " </span><br>\n";
						}
						if (isset($orderlineentries[$i]->comments) && $orderlineentries[$i]->comments!="") {
							echo "<span> <b>Comments: </b>" . $orderlineentries[$i]->comments . " </span><br>\n";
						}
					}
					?>

					</td>
					<td class="text-start table-border-style">@if($orderlineentries[$i]->price!=0) {{"$"}}{{$orderlineentries[$i]->price}} @endif</td>
					<td class="text-end table-border-style">$ {{$orderlineentries[$i]->amount}}</td>
				</tr>
				@endfor
				@endif
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4" style="text-align:right;"><b>Sub Total :</b></td>
				<td colspan="1">$ {{$orders->subTotal}}</td>
			</tr>
			<tr>
				<td colspan="4" style="text-align:right;"><b> Discount :</b></td>
				<td colspan="1">$ {{$orders->discountAmount}}</td>
			</tr>
			<tr>
				<td colspan="4" style="text-align:right;"><b>Tax in(%):</b></td>
				<td colspan="1">{{$orders->taxPer}} </td>
			</tr>
			<tr>
				<td colspan="4" style="text-align:right;"><b>Tax Amount: </b></td>
				<td colspan="1">$ {{$orders->taxAmount}}</td>
			</tr>
			@if($orders->deliveryType!="pickup")
			<tr>
				<td colspan="4"><b>Delivery Charges :</b></td>
				<td colspan="1">$ {{$orders->deliveryCharges}}</td>
			</tr>
			@endif
			<tr>
				<td colspan="4"><b>Grand Total :</b></td>
				<td colspan="1">$ {{$orders->grandTotal}}</td>
			</tr>
		</tfoot>
	</table>
	<table style="display:none;">
		<thead>
			<tr>
				<td colspan="2">
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