@extends('admin.layouts.app')

@section('title', 'Order ' . $order->order_number)
@section('content')
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="font-size:16px;">Order {{ $order->order_number }}</h3>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">Back to Orders</a>
        </div>
        <table style="max-width:600px;">
            <tbody>
                <tr><th>Customer</th><td>{{ $order->user->name }} ({{ $order->user->email ?? $order->user->phone }})</td></tr>
                <tr><th>Subtotal</th><td>₦{{ number_format($order->subtotal, 2) }}</td></tr>
                <tr><th>Delivery Fee</th><td>₦{{ number_format($order->delivery_fee, 2) }}</td></tr>
                <tr><th>Total</th><td>₦{{ number_format($order->total, 2) }}</td></tr>
                <tr><th>Payment Status</th><td>@if($order->payment_status==='SUCCESSFUL')<span class="badge badge-success">Paid</span>@else<span class="badge badge-warning">{{ $order->payment_status }}</span>@endif</td></tr>
                <tr><th>Order Status</th><td><span class="badge badge-info">{{ $order->order_status }}</span></td></tr>
                <tr><th>Created</th><td>{{ $order->created_at?->format('M d, Y H:i') }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-bottom:16px;">Items</h3>
        <table>
            <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($order->items as $item)
                <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>₦{{ number_format($item->unit_price, 2) }}</td><td>₦{{ number_format($item->total, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($order->deliveryInformation)
    <div class="card">
        <h3 style="margin-bottom:16px;">Delivery Information</h3>
        <table style="max-width:600px;">
            <tbody>
                <tr><th>Recipient</th><td>{{ $order->deliveryInformation->recipient_name }}</td></tr>
                <tr><th>Phone</th><td>{{ $order->deliveryInformation->phone }}</td></tr>
                <tr><th>Address</th><td>{{ $order->deliveryInformation->address }}{{ $order->deliveryInformation->city ? ', '.$order->deliveryInformation->city : '' }}</td></tr>
                @if($order->deliveryInformation->additional_notes)<tr><th>Notes</th><td>{{ $order->deliveryInformation->additional_notes }}</td></tr>@endif
            </tbody>
        </table>
    </div>
    @endif

    <div class="card">
        <h3 style="margin-bottom:16px;">Update Order Status</h3>
        <form method="POST" action="{{ route('admin.orders.status', $order) }}">
            @csrf @method('PATCH')
            <div style="display:flex;gap:10px;align-items:center;">
                <select name="order_status" style="padding:10px;border:1px solid #d1d5db;border-radius:6px;">
                    @foreach(['PENDING','PAYMENT_PENDING','PAID','PROCESSING','READY','OUT_FOR_DELIVERY','DELIVERED','CANCELLED'] as $s)
                        <option value="{{ $s }}" @if($order->order_status===$s)selected @endif>{{ $s }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
            <p class="muted" style="margin-top:8px;">Note: Only valid status transitions are permitted.</p>
        </form>
    </div>
@endsection
