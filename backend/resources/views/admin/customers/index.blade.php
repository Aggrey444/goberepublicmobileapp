@extends('admin.layouts.app')

@section('title', 'Customers')
@section('content')
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="font-size:16px;">All Customers ({{ $customers->total() }})</h3>
            <form method="GET" style="display:flex;gap:8px;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, email, phone..." style="padding:8px;border:1px solid #d1d5db;border-radius:6px;width:280px;">
                <button class="btn btn-secondary">Search</button>
            </form>
        </div>
        @if($customers->isEmpty())
            <p class="muted">No customers found.</p>
        @else
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email ?? '—' }}</td>
                    <td>{{ $customer->phone ?? '—' }}</td>
                    <td>{{ $customer->orders_count }}</td>
                    <td>@if($customer->status === 'active')<span class="badge badge-success">Active</span>@else<span class="badge badge-danger">Inactive</span>@endif</td>
                    <td><a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-secondary btn-sm">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $customers->links() }}</div>
        @endif
    </div>
@endsection
