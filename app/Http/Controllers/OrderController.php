<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        // Get current user information
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)->latest()->paginate(10);

        return view('pages.order.list', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::findOrFail($id);

        return view('pages.order.detail', compact('order'));
    }
}
