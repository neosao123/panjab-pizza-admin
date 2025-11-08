<div>
    <h4>Hello, </h4>
    <p>Please find the store order summary for date <strong>{{ $date }}</strong></p>
    <div style="margin:10px;padding:10px;background:#fff8f1">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Store Name</th>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Total Orders</th>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Total Amount</th>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Online Orders</th>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">In-Store Orders</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $item)
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{$item->store_name}}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{$item->total_orders}}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">${{$item->total_amount}}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{$item->online_orders}}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{$item->in_store_orders}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
