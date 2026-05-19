@extends('layouts.user')

@section('title', 'Shopping Cart')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/cart.css') }}">
@endpush

@section('content')
    <div class="cart-container">
        <div class="cart-header">
            <h1 class="cart-title">Shopping Cart</h1>

            @if (session('cart') && count(session('cart')) > 0)
                <form action="{{ route('cart.clear') }}" method="POST"
                    onsubmit="return confirmModal(event, 'Clear Cart', 'Do you want to remove all items from your cart?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-clear-cart">
                        <i class="fa-solid fa-trash-arrow-up"></i> Clear Cart
                    </button>
                </form>
            @endif
        </div>

        @if (session('cart') && count(session('cart')) > 0)
            <div class="cart-wrapper">
                <div class="cart-items">
                    @foreach (session('cart') as $id => $details)
                        <div class="cart-item">
                            <div class="item-img">
                                <img src="{{ $details['image'] ?? 'https://via.placeholder.com/100' }}"
                                    alt="{{ $details['name'] }}">
                            </div>
                            <div class="item-info">
                                <a href="{{ route('products.detail', $id) }}" class="item-name">{{ $details['name'] }}</a>
                                <p class="item-price">{{ number_format($details['price'], 0, ',', '.') }} VND</p>
                                <div class="item-qty">
                                    <form action="{{ route('cart.update', $id) }}" method="POST" class="qty-form">
                                        @csrf
                                        <button type="submit" name="action" value="decrease" class="qty-btn">-</button>

                                        <input type="text" class="qty-input" value="{{ $details['quantity'] }}" readonly>

                                        <button type="submit" name="action" value="increase" class="qty-btn">+</button>
                                    </form>
                                </div>
                            </div>
                            <div class="item-total">
                                {{ number_format($details['price'] * $details['quantity'], 0, ',', '.') }} VND
                            </div>
                            <div class="item-remove">
                                <form
                                    onsubmit="confirmModal(event, 'Remove Product', 'Are you sure you want to remove this product from your cart?', ' ')"
                                    action="{{ route('cart.remove', $id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" id="remove-btn"><i class="fa-regular fa-trash-can"></i></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="cart-summary">
                    <div class="summary-box">
                        <h3>Order Summary</h3>
                        <div class="summary-item">
                            <span>Subtotal</span>
                            <span>{{ number_format($totalAmount, 0, ',', '.') }} VND</span>
                        </div>
                        <div class="summary-item">
                            <span>Shipping</span>
                            <span class="free">Free</span>
                        </div>
                        <hr>
                        <div class="summary-total">
                            <span>Total</span>
                            <span>{{ number_format($totalAmount, 0, ',', '.') }} VND</span>
                        </div>
                        <div class="summary-actions">
                            <a href="{{ route('checkout.index') }}" id="btn-checkout" class="btn-checkout">Proceed to
                                Checkout</a>
                            <a href="{{ route('products.index') }}" class="continue-shopping">Continue Shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="empty-cart">
                <i class="fa-solid fa-cart-shopping"></i>
                <p>Your cart is empty!</p>
                <a href="{{ route('products.index') }}" class="btn-back">Go Shopping</a>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Check authentication từ Server
        const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};

        const btnCheckout = document.getElementById('btn-checkout');

        if (btnCheckout) {
            btnCheckout.addEventListener('click', function(event) {
                event.preventDefault();
                const targetUrl = this.href;

                if (!isLoggedIn) {
                    if (typeof showToast === "function") {
                        showToast("warning", "Please log in to checkout!");
                    } else {
                        alert("You need to log in to checkout!");
                    }
                } else {
                    if (typeof confirmModal === "function") {
                        confirmModal(event, 'Checkout', 'Are you sure you want to checkout?');
                    } else {
                        window.location.href = targetUrl;
                    }
                }
            });
        }
    </script>
@endpush
