<?php

namespace App\Http\Controllers;

use App\Enums\DiscountType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ProductStatus;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\PlaceOrderRequest;
use App\Mail\OrderNotification;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\VNPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = session()->get('cart', []);
        $totalAmount = 0;

        foreach ($cartItems as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        return view('pages.cart', compact('cartItems', 'totalAmount'));
    }

    public function addToCart($id, AddToCartRequest $request)
    {
        $product = Product::findOrFail($id);

        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found!'], 404);
        }

        if (! in_array($product->status, [ProductStatus::APPROVED, ProductStatus::OUT_OF_STOCK], true)) {
            return response()->json(['success' => false, 'message' => 'This product is currently unavailable.'], 403);
        }

        $quantity = (int) $request->input('quantity', 1);
        $cart = session()->get('cart', []);

        // If product already in cart, increase quantity
        if (isset($cart[$id])) {
            // Check max quanity
            $newQuantity = $cart[$id]['quantity'] + $quantity;

            if ($newQuantity > $product->stock_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total quantity in cart exceeds stock!',
                ]);
            }

            $cart[$id]['quantity'] = $newQuantity;
        } else {
            // If not exists, add new item to the array
            $cart[$id] = [
                'name' => $product->name,
                'quantity' => $quantity,
                'price' => $product->price,
                'image' => $product->image_url, // Ensure the column name matches your DB
            ];
        }

        // Save the updated cart to the session
        session()->put('cart', $cart);

        $totalQty = array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['quantity'] ?? 0);
        }, 0);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart!',
            'cart_count' => $totalQty,
        ]);
    }

    public function updateQuantity(Request $request, $id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            if ($request->action == 'increase') {
                $cart[$id]['quantity']++;
            } elseif ($request->action == 'decrease' && $cart[$id]['quantity'] > 1) {
                $cart[$id]['quantity']--;
            } elseif ($request->action == 'decrease' && $cart[$id]['quantity'] == 1) {
                unset($cart[$id]); // Remove item if quantity is 1 and user tries to decrease
            }

            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Quantity updated!');
    }

    public function removeFromCart($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Item removed from cart!');
    }

    public function clearCart()
    {
        session()->forget('cart');

        return redirect()->back()->with('success', 'Cart cleared!');
    }

    public function checkout()
    {
        $cartItems = session()->get('cart', []);
        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        $user = auth()->user();
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        return view('pages.payment', compact('cartItems', 'totalAmount', 'user'));
    }

    public function applyDiscount(Request $request)
    {
        $code = $request->input('code');
        $subtotal = $request->input('subtotal');

        $discount = Discount::where('code', $code)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $discount) {
            return response()->json(['success' => false, 'message' => 'This discount code does not exist or has expired.']);
        }

        if ($discount->usage_limit !== null && $discount->used_count >= $discount->usage_limit) {
            return response()->json(['success' => false, 'message' => 'This discount code has reached its usage limit.']);
        }

        if ($subtotal < $discount->min_order_value) {
            return response()->json(['success' => false, 'message' => 'At least '.number_format($discount->min_order_value, 0, ',', '.').' VND is required to apply this discount code.']);
        }

        $discountAmount = 0;
        if ($discount->type === DiscountType::FIXED) {
            $discountAmount = (float) $discount->value;
        } else {
            $discountAmount = ($subtotal * (float) $discount->value) / 100;
            if ($discount->max_discount_amount !== null && $discountAmount > $discount->max_discount_amount) {
                $discountAmount = (float) $discount->max_discount_amount;
            }
        }

        return response()->json([
            'success' => true,
            'discount_amount' => $discountAmount,
            'discount_id' => $discount->id,
            'message' => 'Applied discount successfully!',
        ]);
    }

    public function placeOrder(PlaceOrderRequest $request, VNPayService $vnpayService)
    {
        // 1. Take cart from Session
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        // 3. Use Database Transaction to ensure safety
        // If saving product fails, order information will also be canceled (no database clutter)
        DB::beginTransaction();

        try {
            $totalPrice = 0;
            $totalQuantity = 0;

            foreach ($cart as $details) {
                $totalPrice += $details['price'] * $details['quantity'];
                $totalQuantity += $details['quantity'];
            }

            $appliedDiscountId = null;
            $appliedDiscountCode = null;
            $appliedDiscountValue = 0;

            // Check if there's a discount code applied and validate it again before saving to the database
            if ($request->filled('discount_id')) {
                $discount = Discount::find($request->discount_id);

                if ($discount && $discount->is_active &&
                    ($discount->expires_at == null || $discount->expires_at > now()) &&
                    ($totalPrice >= $discount->min_order_value)) {

                    $discountAmount = 0;
                    if ($discount->type === DiscountType::FIXED) {
                        $discountAmount = (float) $discount->value;
                    } else {
                        $discountAmount = ($totalPrice * (float) $discount->value) / 100;
                        if ($discount->max_discount_amount !== null && $discountAmount > $discount->max_discount_amount) {
                            $discountAmount = (float) $discount->max_discount_amount;
                        }
                    }

                    $appliedDiscountId = $discount->id;
                    $appliedDiscountCode = $discount->code;
                    $appliedDiscountValue = $discountAmount;
                    $totalPrice = max(0, $totalPrice - $discountAmount);
                    $discount->increment('used_count');
                }
            }

            // 4. Save to 'orders' table
            $order = Order::create([
                'user_id' => Auth::id(),
                'discount_id' => $appliedDiscountId,
                'discount_code' => $appliedDiscountCode,
                'discount_value' => $appliedDiscountValue,
                'total_quantity' => $totalQuantity,
                'total_price' => $totalPrice, // Giá đã trừ discount
                'receiver_name' => $request->name,
                'receiver_phone' => $request->phone,
                'receiver_address' => $request->address,
                'note' => $request->note,
                'payment_method' => $request->payment_method,
                'status' => OrderStatus::PENDING,
            ]);

            // 5. Save to 'order_items' table (Details of each product)
            foreach ($cart as $id => $details) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $id,
                    'quantity' => $details['quantity'],
                    'price' => $details['price'],
                ]);
            }

            // 6. PAYMENT STREAMING
            if ($request->payment_method === PaymentMethod::VNPAY->value) {
                // VNPAY CASE:
                // - Do not deduct from storage immediately.
                // - Commit to save order int DB and go to pay.
                DB::commit();

                $paymentUrl = $vnpayService->createVnpayPayment($order);

                return redirect()->away($paymentUrl);
            } else {
                // COD CASE:
                // Update Order Status
                $order->update(['status' => OrderStatus::PROCESSING]);

                // - Deduct from storage immediately.
                foreach ($cart as $id => $details) {
                    $product = Product::lockForUpdate()->find($id);
                    $quantity = $details['quantity'];

                    if ($product) {
                        DB::transaction(function () use ($product, $quantity) {
                            $product->decrement('stock_quantity', $quantity);

                            if ($product->stock_quantity <= 0) {
                                ProductStatusLog::create([
                                    'product_id' => $product->id,
                                    'old_status' => $product->status,
                                    'new_status' => ProductStatus::OUT_OF_STOCK,
                                    'reason' => 'Stock quantity reached 0',
                                    'changed_by' => null,
                                    'changed_by_role' => 'system',
                                ]);

                                $product->status = ProductStatus::OUT_OF_STOCK;
                                $product->save();
                            }
                        });
                    }
                }

                session()->forget('cart');
                DB::commit();

                // Send mail to confirm for COD payment
                try {
                    Mail::to(Auth::user()->email)->send(new OrderNotification($order));
                } catch (\Exception $e) {
                    \Log::error('Mail error: '.$e->getMessage());
                }

                return redirect()->route('checkout.success', $order->id)
                    ->with('success', 'Order placed successfully!');
            }
        } catch (\Exception $e) {
            // If any error occurs, rollback the transaction to prevent database clutter
            DB::rollBack();

            return redirect()->back()->with('error', 'System Error: '.$e->getMessage());
        }
    }

    public function vnpayReturn(Request $request, VNPayService $vnpayService)
    {
        return $vnpayService->vnpayReturn($request);
    }

    public function orderSuccess($id)
    {
        $order = Order::with('items.product')->findOrFail($id);

        // Check if the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('pages.order-success', compact('order'));
    }
}
