<!-- Developer: Shreyas Mahamuni, Working Date: 30-11-2023 -->
<!-- This excel view for STR_1  -->
<table>
    <thead>
        <tr>
            <th colspan="4" style="text-align: center;">Reports By Store Location</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center;">{{$storeLocation->storeLocation}}</th>
        </tr>
        <tr>
            <th><b>Order Number</b></th>
            <th><b>Delivery Type</b></th>
            <th><b>Order From</b></th>
            <th><b>Grand Total</b></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($orders as $order)
        <tr>
            <td>{{ $order->orderCode }}</td>
            <td>{{ $order->deliveryType }}</td>
            <td>{{ $order->orderFrom }}</td>
            <td>{{ $order->grandTotal }}</td>
        </tr>
        @endforeach
    </tbody>
</table>