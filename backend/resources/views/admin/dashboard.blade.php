@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('content')
    <div class="grid grid-4">
        <div class="stat" style="background:#f59e0b;"><div class="value">{{ $totalOrders }}</div><div class="label">Total Orders</div></div>
        <div class="stat" style="background:#6b7280;"><div class="value">{{ $pendingOrders }}</div><div class="label">Pending Orders</div></div>
        <div class="stat" style="background:#10b981;"><div class="value">{{ $paidOrders }}</div><div class="label">Paid Orders</div></div>
        <div class="stat" style="background:#3b82f6;"><div class="value">{{ $processingOrders }}</div><div class="label">Processing</div></div>
        <div class="stat" style="background:#8b5cf6;"><div class="value">{{ $deliveredOrders }}</div><div class="label">Delivered</div></div>
        <div class="stat" style="background:#ef4444;"><div class="value">{{ $totalCustomers }}</div><div class="label">Customers</div></div>
        <div class="stat" style="background:#14b8a6;"><div class="value">{{ $totalProducts }}</div><div class="label">Products</div></div>
        <div class="stat" style="background:#d97706;"><div class="value">₦{{ number_format($revenue, 2) }}</div><div class="label">Revenue (Paid)</div></div>
    </div>

    <div class="card" style="margin-top:20px;">
        <h3 style="margin-bottom:16px;">Recent Orders</h3>
        @if($recentOrders->isEmpty())
            <p class="muted">No orders yet.</p>
        @else
        <table>
            <thead>
                <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($recentOrders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->user->name }}</td>
                    <td>₦{{ number_format($order->total, 2) }}</td>
                    <td>
                        @if($order->payment_status === 'SUCCESSFUL') <span class="badge badge-success">Paid</span>
                        @else <span class="badge badge-warning">{{ $order->payment_status }}</span> @endif
                    </td>
                    <td><span class="badge badge-info">{{ $order->order_status }}</span></td>
                    <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary btn-sm">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
@endsection
