<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStatusLog;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request, $shop_id)
    {
        $query = Product::with('category')
            ->where('shop_id', $shop_id);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = (clone $query)
            ->whereNot('status', ProductStatus::PENDING)
            ->latest()->paginate(10)->withQueryString();

        $pendingProducts = (clone $query)
            ->where('status', ProductStatus::PENDING)
            ->latest()->get();

        $categories = Category::all();
        $shop = Shop::findOrFail($shop_id);

        return view('admin.products.index', compact('products', 'pendingProducts', 'categories', 'shop'));
    }

    public function search(Request $request, $shop_id)
    {
        $searchTerm = $request->input('search');

        $query = Product::with('category')
            ->where('shop_id', $shop_id);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = (clone $query)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%'.$searchTerm.'%')
                    ->orWhere('sku', 'like', '%'.$searchTerm.'%');
            })
            ->whereNot('status', ProductStatus::PENDING)
            ->latest()
            ->paginate(10);

        $pendingProducts = (clone $query)
            ->where('status', ProductStatus::PENDING)
            ->latest()
            ->get();

        $categories = Category::all();
        $shop = Shop::findOrFail($shop_id);

        return view('admin.products.index', compact('products', 'pendingProducts', 'categories', 'shop'));
    }

    public function updateStatusToApproved(Product $product)
    {
        if ($product->status !== ProductStatus::PENDING) {
            return redirect()->back()->with('error', 'Invalid product status.');
        }

        DB::transaction(function () use ($product) {
            ProductStatusLog::create([
                'product_id' => $product->id,
                'old_status' => $product->status,
                'new_status' => ProductStatus::APPROVED,
                'reason' => 'Approved by admin',
                'changed_by' => auth()->id(),
                'changed_by_role' => UserRole::ADMIN,
            ]);

            $product->status = ProductStatus::APPROVED;
            $product->save();
        });

        return redirect()->back()->with('success', 'Product status updated to approved successfully!');
    }

    public function updateStatusToRejected(Product $product)
    {
        if ($product->status !== ProductStatus::PENDING) {
            return redirect()->back()->with('error', 'Invalid product status.');
        }

        DB::transaction(function () use ($product) {
            ProductStatusLog::create([
                'product_id' => $product->id,
                'old_status' => $product->status,
                'new_status' => ProductStatus::REJECTED,
                'reason' => 'Rejected by admin',
                'changed_by' => auth()->id(),
                'changed_by_role' => UserRole::ADMIN,
            ]);

            $product->status = ProductStatus::REJECTED;
            $product->save();
        });

        return redirect()->back()->with('success', 'Product status updated to rejected successfully!');
    }

    public function updateStatusToHidden(Product $product)
    {
        if ($product->status === ProductStatus::HIDDEN) {
            return redirect()->back()->with('error', 'This product is already hidden.');
        }

        DB::transaction(function () use ($product) {
            ProductStatusLog::create([
                'product_id' => $product->id,
                'old_status' => $product->status,
                'new_status' => ProductStatus::HIDDEN,
                'reason' => 'Hidden by admin',
                'changed_by' => auth()->id(),
                'changed_by_role' => UserRole::ADMIN,
            ]);

            $product->status = ProductStatus::HIDDEN;
            $product->save();
        });

        return redirect()->back()->with('success', 'Product status updated to hidden successfully.');
    }

    public function updateStatusToVisible(Product $product)
    {
        if ($product->status !== ProductStatus::HIDDEN) {
            return redirect()->back()->with('error', 'Invalid product status.');
        }

        $lastLog = ProductStatusLog::where('product_id', $product->id)
            ->latest()
            ->first();

        if (! $lastLog) {
            return redirect()->back()->with('error', 'No status log found for this product.');
        }

        $oldStatus = $product->status;
        $restoredStatus = $lastLog->old_status;

        DB::transaction(function () use ($product, $oldStatus, $restoredStatus) {
            ProductStatusLog::create([
                'product_id' => $product->id,
                'old_status' => $oldStatus,
                'new_status' => $restoredStatus,
                'reason' => 'Restored by admin',
                'changed_by' => auth()->id(),
                'changed_by_role' => UserRole::ADMIN,
            ]);

            $product->status = $restoredStatus;
            $product->save();
        });

        return redirect()->back()->with('success', 'Product status restored successfully.');
    }
}
