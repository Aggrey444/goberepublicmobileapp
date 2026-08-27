<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $data = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('order_status', Order::ORDER_STATUS_PENDING)->count(),
            'paid_orders' => Order::where('payment_status', Order::PAYMENT_STATUS_SUCCESSFUL)->count(),
            'processing_orders' => Order::where('order_status', Order::ORDER_STATUS_PROCESSING)->count(),
            'delivered_orders' => Order::where('order_status', Order::ORDER_STATUS_DELIVERED)->count(),
            'total_customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
            'total_products' => Product::where('status', Product::STATUS_ACTIVE)->count(),
            'revenue' => (float) Order::where('payment_status', Order::PAYMENT_STATUS_SUCCESSFUL)->sum('total'),
        ];

        return ApiResponse::success('Dashboard statistics retrieved successfully.', $data);
    }
}
