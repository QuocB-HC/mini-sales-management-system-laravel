<?php

namespace App\Http\Controllers\Seller;

use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStatusLog;
use App\Models\Shop;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request, $shopId = null)
    {
        $userId = auth()->id();

        $shops = Shop::where('user_id', $userId)->get();

        if ($shops->isEmpty()) {
            return redirect()->route('shop.create')->with('error', 'You must create a shop first.');
        }

        $currentShop = $shopId
            ? $shops->firstWhere('id', $shopId)
            : $shops->first();

        if (! $currentShop) {
            return abort(403, 'You are not allowed to access this shop.');
        }

        $products = Product::with('category')
            ->where('shop_id', $currentShop->id)
            ->latest()
            ->paginate(10);

        return view('seller.products.index', compact('shops', 'currentShop', 'products'));
    }

    public function create($shopId)
    {
        $currentShopId = $shopId ? $shopId : null;
        $categories = Category::all();

        if ($currentShopId == null) {
            return redirect()->route('seller.shop.index')->with('error', 'You must select a shop first.');
        }

        return view('seller.products.create', compact('currentShopId', 'categories'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        $result = $cloudinary->upload(
            $request->file('image')->getRealPath(),
            CloudinaryService::FOLDER_PRODUCT
        );

        $user->update([
            'avatar_url' => $result['url'],
            'avatar_public_id' => $result['public_id'],
        ]);

        Product::create($data);

        return redirect()->route('seller.products.index')->with('success', 'Product created successfully.');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();

        if ($product->shop->user_id !== auth()->id()) {
            return abort(403, 'You are not allowed to edit this product.');
        }

        return view('seller.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->shop->user_id !== auth()->id()) {
            return abort(403, 'You are not allowed to update this product.');
        }

        $data = $request->validated();
        $oldStatus = $product->status;
        $newStatus = null;

        if ($product->status === ProductStatus::OUT_OF_STOCK && $data['stock_quantity'] > 0) {
            $newStatus = ProductStatus::APPROVED;
            $data['status'] = $newStatus;
        } elseif ($product->status === ProductStatus::APPROVED && $data['stock_quantity'] == 0) {
            $newStatus = ProductStatus::OUT_OF_STOCK;
            $data['status'] = $newStatus;
        }

        DB::transaction(function () use ($product, $data, $oldStatus, $newStatus) {
            $product->update($data);

            if ($newStatus !== null) {
                ProductStatusLog::create([
                    'product_id' => $product->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'reason' => 'Stock quantity updated by seller',
                    'changed_by' => auth()->id(),
                    'changed_by_role' => UserRole::SELLER,
                ]);
            }
        });

        return redirect()->route('seller.products.index')->with('success', 'Product updated successfully.');
    }

    public function updateStatusToHidden(Product $product)
    {
        if ($product->shop->user_id !== auth()->id()) {
            return abort(403, 'You are not allowed to update this product.');
        }

        $oldStatus = $product->status;
        $product->update(['status' => ProductStatus::HIDDEN]);

        ProductStatusLog::create([
            'product_id' => $product->id,
            'old_status' => $oldStatus,
            'new_status' => ProductStatus::HIDDEN,
            'reason' => 'Hidden by seller',
            'changed_by' => auth()->id(),
            'changed_by_role' => UserRole::SELLER,
        ]);

        return redirect()->back()->with('success', 'Product status updated to hidden successfully.');
    }

    public function updateStatusToVisible(Product $product)
    {
        if ($product->shop->user_id !== auth()->id()) {
            return abort(403, 'You are not allowed to update this product.');
        }

        $lastLog = ProductStatusLog::where('product_id', $product->id)
            ->latest()
            ->first();

        $oldStatus = $product->status;
        $restoredStatus = $lastLog->old_status;

        $product->update(['status' => $restoredStatus]);

        ProductStatusLog::create([
            'product_id' => $product->id,
            'old_status' => $oldStatus,
            'new_status' => $restoredStatus,
            'reason' => 'Restored by seller',
            'changed_by' => auth()->id(),
            'changed_by_role' => UserRole::SELLER,
        ]);

        return redirect()->back()->with('success', 'Product status restored successfully.');
    }

    public function updateAvatar(Request $request, CloudinaryService $cloudinary)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $user = auth()->user();

        if ($user->avatar_public_id) {
            $cloudinary->delete($user->avatar_public_id);
        }

        $result = $cloudinary->upload(
            $request->file('image')->getRealPath(),
            CloudinaryService::FOLDER_PRODUCT
        );

        $user->update([
            'avatar_url' => $result['url'],
            'avatar_public_id' => $result['public_id'],
        ]);

        return back()->with('success', 'Update product image successfully!');
    }
}
