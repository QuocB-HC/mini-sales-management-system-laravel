<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreShopRequest;
use App\Http\Requests\Shop\UpdateShopRequest;
use App\Models\Shop;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($shopId = null)
    {
        $userId = auth()->id();
        $shops = Shop::where('user_id', $userId)->get();

        $shop = $shopId ? Shop::find($shopId) : $shops->first();

        return view('seller.shops.index', compact('shops', 'shop'));
    }
}
