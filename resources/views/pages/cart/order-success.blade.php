@extends('layouts.user', ['hideHeaderFooter' => true])

@section('title', 'Order Success')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/order-success.css') }}">
@endpush

@section('content')
    <div class="success-container">
        <div class="success-card">
            <div class="icon-box">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <h1>Thank you for your purchase!</h1>
            <p>Your order has been received and is being processed.</p>

            <div class="order-details">
                <div class="detail-item">
                    <span>Order ID:</span>
                    <strong>#ORD-{{ $order->id }}</strong>
                </div>
                <div class="detail-item">
                    <span>Total Amount:</span>
                    <strong>{{ number_format($order->total_price, 0, ',', '.') }} VND</strong>
                </div>
                <div class="detail-item">
                    <span>Payment Method:</span>
                    <strong>{{ $order->payment_method->value === 'cod' ? 'Cash on Delivery (COD)' : 'Bank Transfer' }}</strong>
                </div>
            </div>

            <div class="shipping-address">
                <h3><i class="fa-solid fa-location-dot"></i> Shipping Address:</h3>
                <p>{{ $order->receiver_name }} | {{ $order->receiver_phone }}</p>
                <p>{{ $order->receiver_address }}</p>
            </div>

            <div class="action-buttons">
                <a href="/" class="btn-home">Confirm</a>
            </div>
        </div>
    </div>
@endsection
