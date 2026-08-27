@extends('admin.layouts.app')

@section('title', 'Orders')
@section('content')
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
            <h3 style="font-size:16px;">All Orders ({{ $orders->total() }})</h3>
            <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Order number..." style="padding:8px;border:1px solid #d1d5db;border-radius:6px;">
                <select name="order_status" style="padding:8px;border:1px solid #d1d5db;border-radius:6px;">
                    <option value="">All statuses</option>
                    @foreach(['PENDING','PAYMENT_PENDING','PAID','PROCESSING','READY','OUT_FOR_DELIVERY','DELIVERED','CANCELLED'] as $s)
                        <option value="{{ $s }}" @if(request('order_status')===$s)selected @endif>{{ $s }}</option>
                    @endforeach
                </select>
                <select name="payment_status" style="padding:8px;border:1px solid #d1d5db;border-radius:6px;">
                    <option value="">All payments</option>
                    @foreach(['PENDING','SUCCESSFUL','FAILED','CANCELLED','REFUNDED'] as $s)
                        <option value="{{ $s }}" @if(request('payment_status')===$s)selected @endif>{{ $s }}</option>
                    @endforeach
                </select>
                <button class="btn btn-secondary">Filter</button>
            </form>
        </div>
        @if($orders->isEmpty())
            <p class="muted">No orders found.</p>
        @else
        <table>
            <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->user->name }}</td>
                    <td>₦{{ number_format($order->total, 2) }}</td>
                    <td>@if($order->payment_status==='SUCCESSFUL')<span class="badge badge-success">Paid</span>@else<span class="badge badge-warning">{{ $order->payment_status }}</span>@endif</td>
                    <td><span class="badge badge-info">{{ $order->order_status }}</span></td>
                    <td>{{ $order->created_at?->format('M d, Y H:i') }}</td>
                    <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary btn-sm">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
