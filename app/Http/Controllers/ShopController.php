<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Http\Requests\StoreShopRequest;
use App\Models\Product;
use App\Models\Shop;

class ShopController extends Controller
{
    public function index($id)
    {
        $shop = Shop::findOrFail($id);

        $products = Product::where('shop_id', $shop->id)
            ->where('status', ProductStatus::APPROVED)
            ->orWhere('status', ProductStatus::OUT_OF_STOCK)
            ->latest()
            ->paginate(12);

        return view('pages.shop.detail', compact('shop', 'products'));
    }

    public function create()
    {
        return view('pages.shop.create');
    }

    public function store(StoreShopRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        Shop::create($data);

        return redirect()->route('home')->with('success', 'Shop information saved successfully.');
    }
}
