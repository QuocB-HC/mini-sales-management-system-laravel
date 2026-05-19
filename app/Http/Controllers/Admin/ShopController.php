<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ShopStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::with('user')->where('status', ShopStatus::APPROVED)->latest()->paginate(10);

        $pendingShops = Shop::with('user')->where('status', ShopStatus::PENDING)->oldest()->paginate(10);

        return view('admin.shops.index', compact('shops', 'pendingShops'));
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('search');

        $shops = Shop::with('user')
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%'.$searchTerm.'%')
                    ->orWhereHas('user', function ($query) use ($searchTerm) {
                        $query->where('email', 'like', '%'.$searchTerm.'%');
                    });
            })
            ->where('status', ShopStatus::APPROVED)
            ->latest()
            ->paginate(10);

        $pendingShops = Shop::with('user')->where('status', ShopStatus::PENDING)->oldest()->paginate(10);

        return view('admin.shops.index', compact('shops', 'pendingShops'));
    }

    public function updateStatusToApproved(Shop $shop)
    {
        if ($shop->status === ShopStatus::PENDING) {
            $shop->status = ShopStatus::APPROVED;
            $shop->save();

            if ($shop->user->role === UserRole::CUSTOMER) {
                $shop->user->role = 'seller';
                $shop->user->save();
            }

            return redirect()->back()->with('success', 'Shop approved successfully.');
        }

        return redirect()->back()->with('error', 'Invalid shop status.');
    }

    public function updateStatusToRejected(Shop $shop)
    {
        if ($shop->status === ShopStatus::PENDING) {
            $shop->status = ShopStatus::REJECTED;
            $shop->save();

            return redirect()->back()->with('success', 'Shop rejected successfully.');
        }

        return redirect()->back()->with('error', 'Invalid shop status.');
    }
}
