@extends('layouts.user')

@section('title', 'Payment Information')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/payment.css') }}">
@endpush

@section('content')
    <div class="payment-container">
        <form onsubmit="confirmModal(event, 'Place Order Confirm', 'Are you sure to place order now?')"
            action="{{ route('checkout.placeOrder') }}" method="POST" class="payment-wrapper">
            @csrf
            <input type="hidden" name="discount_id" id="applied_discount_id">

            <div class="shipping-info">
                <h2><i class="fa-solid fa-truck"></i> Shipping Information</h2>

                <div class="info-card">
                    <div class="input-group">
                        <label>Receiver Name</label>
                        <input type="text" name="name" value="{{ $user->name }}" required>
                    </div>

                    <div class="input-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="{{ $user->phone }}" placeholder="Enter phone number"
                            required>
                    </div>

                    <div class="input-group">
                        <label>Shipping Address</label>
                        <textarea name="address" rows="3" required placeholder="Enter your full address">{{ $user->address }}</textarea>
                    </div>

                    <div class="input-group">
                        <label>Note</label>
                        <textarea name="note" rows="10" placeholder="Enter your note"></textarea>
                    </div>
                </div>

                <div class="payment-methods">
                    <h2><i class="fa-solid fa-credit-card"></i> Payment Method</h2>
                    <div class="method-options">
                        <label class="method-item">
                            <input type="radio" name="payment_method" value="cod" checked>
                            <span class="checkmark"></span>
                            <i class="fa-solid fa-money-bill-wave"></i> Cash on Delivery (COD)
                        </label>
                        <label class="method-item">
                            <input type="radio" name="payment_method" value="vnpay">
                            <span class="checkmark"></span>
                            <i class="fa-solid fa-building-columns"></i> Bank Transfer
                        </label>
                    </div>
                </div>
            </div>

            <div class="order-summary">
                <div class="summary-card">
                    <h3>Order Summary</h3>
                    <div class="item-list">
                        @foreach ($cartItems as $item)
                            <div class="summary-item">
                                <span>{{ $item['name'] }} (x{{ $item['quantity'] }})</span>
                                <span>{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} VND</span>
                            </div>
                        @endforeach
                    </div>

                    <hr>

                    <div class="total-row">
                        <span>Subtotal</span>
                        <span id="subtotal_value"
                            data-value="{{ $totalAmount }}">{{ number_format($totalAmount, 0, ',', '.') }} VND</span>
                    </div>

                    <div class="discount-input-row">
                        <input type="text" id="discount_code" class="discount-input" placeholder="Enter discount code">
                        <button type="button" class="btn-apply-discount" id="apply_discount">Apply</button>
                    </div>

                    <div class="total-row">
                        <span>Total Quantity</span>
                        <span>{{ array_sum(array_column($cartItems, 'quantity')) }}</span>
                    </div>
                    <div class="total-row">
                        <span>Shipping Fee</span>
                        <span class="free">Free</span>
                    </div>
                    <div class="total-row">
                        <span>Total Pay</span>
                        <span class="free">{{ number_format($totalAmount, 0, ',', '.') }} VND</span>
                    </div>
                    <div class="total-row discount-row" style="color: #d91e18;">
                        <span>Discount</span>
                        <span id="discount_amount">- 0 VND</span>
                    </div>
                    <div class="total-row final">
                        <span>Final Pay</span>
                        <span id="final_pay_display" data-base="{{ $totalAmount }}">
                            {{ number_format($totalAmount, 0, ',', '.') }} VND</span>
                    </div>

                    <button type="submit" class="btn-confirm">Place Order Now</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('apply_discount').addEventListener('click', function() {
            const code = document.getElementById('discount_code').value;
            const subtotal = parseFloat(document.getElementById('subtotal_value').dataset.value);
            const messageDiv = document.getElementById('discount_message');
            const discountAmountSpan = document.getElementById('discount_amount');
            const finalPayDisplay = document.getElementById('final_pay_display');
            const appliedDiscountIdInput = document.getElementById('applied_discount_id');

            if (!code || code.trim() === "") {
                if (typeof showToast === "function") {
                    showToast("error", "Please enter discount code first!");
                } else {
                    alert("Please enter discount code first!");
                }
                return;
            }

            fetch('{{ route('checkout.applyDiscount') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        code: code,
                        subtotal: subtotal
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof showToast === "function") {
                            showToast("success", data.message);
                        } else {
                            alert(data.message);
                        }

                        const discountAmount = data.discount_amount;
                        const finalTotal = subtotal - discountAmount;

                        discountAmountSpan.textContent =
                            `- ${new Intl.NumberFormat('vi-VN').format(discountAmount)} VND`;
                        finalPayDisplay.textContent =
                            `${new Intl.NumberFormat('vi-VN').format(finalTotal)} VND`;
                        appliedDiscountIdInput.value = data.discount_id;
                    } else {
                        if (typeof showToast === "function") {
                            showToast("error", data.message);
                        } else {
                            alert(data.message);
                        }

                        // Reset tp - 0 VND if discount code is invalid
                        discountAmountSpan.textContent = `- 0 VND`;
                        finalPayDisplay.textContent = `${new Intl.NumberFormat('vi-VN').format(subtotal)} VND`;
                        appliedDiscountIdInput.value = '';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof showToast === "function") {
                        showToast("error", "Error. Please try again!");
                    } else {
                        alert("Error. Please try again!");
                    }
                });
        });
    </script>
@endpush
