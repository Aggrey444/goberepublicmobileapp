<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GOBE Republic Admin')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f3f4f6; color: #1f2937; }
        .layout { display: flex; min-height: 100vh; }
        .sidebar { width: 240px; background: #111827; color: #d1d5db; flex-shrink: 0; display: flex; flex-direction: column; }
        .sidebar .brand { padding: 20px; font-size: 18px; font-weight: 700; color: #fff; border-bottom: 1px solid #1f2937; }
        .sidebar .brand span { color: #f59e0b; }
        .sidebar nav { flex: 1; padding: 12px 0; }
        .sidebar nav a { display: block; padding: 12px 20px; color: #d1d5db; text-decoration: none; font-size: 14px; }
        .sidebar nav a:hover, .sidebar nav a.active { background: #1f2937; color: #fff; }
        .sidebar .sidebar-foot { padding: 16px 20px; border-top: 1px solid #1f2937; font-size: 13px; }
        .main { flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #fff; padding: 14px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .topbar h1 { font-size: 18px; font-weight: 600; }
        .content { padding: 24px; flex: 1; }
        .card { background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; padding: 20px; margin-bottom: 20px; }
        .grid { display: grid; gap: 16px; }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .stat { padding: 16px; border-radius: 8px; color: #fff; }
        .stat .value { font-size: 28px; font-weight: 700; }
        .stat .label { font-size: 13px; opacity: .9; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        th { background: #f9fafb; font-weight: 600; color: #6b7280; text-transform: uppercase; font-size: 12px; }
        .btn { display: inline-block; padding: 8px 14px; border-radius: 6px; font-size: 14px; cursor: pointer; border: none; text-decoration: none; }
        .btn-primary { background: #f59e0b; color: #fff; }
        .btn-secondary { background: #6b7280; color: #fff; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .muted { color: #6b7280; font-size: 13px; }
        .img-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 6px; }
        .actions { display: flex; gap: 6px; }
        form.inline { display: inline; }
        .pagination { margin-top: 16px; }
        .pagination nav div:first-child { margin-bottom: 8px; }
        @media (max-width: 900px) { .grid-4 { grid-template-columns: repeat(2, 1fr); } .grid-3 { grid-template-columns: 1fr; } .sidebar { display: none; } }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">GOBE <span>Republic</span></div>
        <nav>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Products</a>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Categories</a>
            <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">Customers</a>
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">Orders</a>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Admin Users</a>
        </nav>
        <div class="sidebar-foot">
            <div style="margin-bottom:8px;">Signed in as <strong>{{ auth()->user()->name }}</strong></div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">Logout</button>
            </form>
        </div>
    </aside>
    <div class="main">
        <div class="topbar">
            <h1>@yield('title', 'GOBE Republic Admin')</h1>
            <span class="muted">{{ now()->format('M d, Y') }}</span>
        </div>
        <div class="content">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
