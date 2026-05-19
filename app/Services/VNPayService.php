<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Mail\OrderNotification;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class VNPayService
{
    public function createVnpayPayment($order)
    {
        $vnp_Config = config('vnpay');

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $vnp_Config['vnp_TmnCode'],
            'vnp_Amount' => $order->total_price * 100, // Use the actual price from the order you just created
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => request()->ip(),
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => 'Pay the order #'.$order->id,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => $vnp_Config['vnp_Returnurl'],
            'vnp_TxnRef' => $order->id, // Use Order ID used as a reference code
        ];

        ksort($inputData);
        $query = '';
        $i = 0;
        $hashdata = '';
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashdata .= urlencode($key).'='.urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key).'='.urlencode($value).'&';
        }

        $vnp_Url = $vnp_Config['vnp_Url'].'?'.$query;
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_Config['vnp_HashSecret']);
        $vnp_Url .= 'vnp_SecureHash='.$vnpSecureHash;

        return $vnp_Url;
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = $request->except('vnp_SecureHash'); // Get all except hash

        ksort($inputData);
        $hashData = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData.'&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashData = $hashData.urlencode($key).'='.urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, config('vnpay.vnp_HashSecret'));

        if ($secureHash == $vnp_SecureHash) {
            $orderId = $request->vnp_TxnRef;
            $order = Order::findOrFail($orderId);

            if ($request->vnp_ResponseCode == '00') {
                DB::transaction(function () use ($order) {
                    $order->update(['status' => OrderStatus::PROCESSING]);

                    foreach ($order->items as $item) {
                        $product = Product::lockForUpdate()->findOrFail($item->product_id);
                        $oldStatus = $product->status;
                        $product->decrement('stock_quantity', $item->quantity);

                        if ($product->stock_quantity <= 0) {
                            ProductStatusLog::create([
                                'product_id' => $product->id,
                                'old_status' => $oldStatus,
                                'new_status' => ProductStatus::OUT_OF_STOCK,
                                'reason' => 'Stock quantity reached 0',
                                'changed_by' => null,
                                'changed_by_role' => 'system',
                            ]);

                            $product->status = ProductStatus::OUT_OF_STOCK;
                            $product->save();
                        }
                    }
                });

                session()->forget('cart');
                Mail::to($order->user->email)->send(new OrderNotification($order));

                return redirect()->route('checkout.success', $order->id);
            } else {
                // If the payment fails, you can keep the status as 'pending'
                // Or change to 'failed'
                return redirect()->route('cart.index')->with('error', 'VNPay payment failed.');
            }
        }

        return abort(403, 'Invalid signature!');
    }
}
