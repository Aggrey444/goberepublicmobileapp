<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::query()->with(['user', 'items']);

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->string('order_status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status'));
        }

        if ($request->filled('q')) {
            $query->where('order_number', 'like', '%' . $request->string('q') . '%');
        }

        $orders = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items', 'deliveryInformation', 'payment']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_status' => ['required', 'in:PENDING,PAYMENT_PENDING,PAID,PROCESSING,READY,OUT_FOR_DELIVERY,DELIVERED,CANCELLED'],
        ]);

        $target = $validated['order_status'];

        if (!$order->canTransitTo($target)) {
            return back()->with('error', "Cannot transition order from {$order->order_status} to {$target}.");
        }

        $order->update(['order_status' => $target]);

        return back()->with('status', 'Order status updated successfully.');
    }
}
