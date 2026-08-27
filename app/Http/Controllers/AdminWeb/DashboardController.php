<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $data = [
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('order_status', Order::ORDER_STATUS_PENDING)->count(),
            'paidOrders' => Order::where('payment_status', Order::PAYMENT_STATUS_SUCCESSFUL)->count(),
            'processingOrders' => Order::where('order_status', Order::ORDER_STATUS_PROCESSING)->count(),
            'deliveredOrders' => Order::where('order_status', Order::ORDER_STATUS_DELIVERED)->count(),
            'totalCustomers' => User::where('role', User::ROLE_CUSTOMER)->count(),
            'totalProducts' => Product::count(),
            'revenue' => (float) Order::where('payment_status', Order::PAYMENT_STATUS_SUCCESSFUL)->sum('total'),
            'recentOrders' => Order::with('user')->orderByDesc('created_at')->limit(8)->get(),
        ];

        return view('admin.dashboard', $data);
    }
}
