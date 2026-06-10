<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Customer dashboard: list past orders and track status.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('dashboard.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        // Ownership guard — customers may only view their own orders.
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items');

        return view('dashboard.order', compact('order'));
    }
}
