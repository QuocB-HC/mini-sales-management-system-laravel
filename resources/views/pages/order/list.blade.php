@extends('layouts.user')

@section('title', 'Order History')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/order-history.css') }}">
@endpush

@section('content')
    <div class="order-history-container">
        <h1><i class="fa-solid fa-clock-rotate-left"></i> Your Order History</h1>

        @if ($orders->isEmpty())
            <p class="no-orders">You haven't placed any orders yet.</p>
        @else
            <div class="order-list">
                @foreach ($orders as $order)
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-id">Order #{{ $order->id }}</span>
                            <span class="order-date">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="order-body">
                            <p><strong>Total Items:</strong> {{ $order->total_quantity }}</p>
                            <p><strong>Total Price:</strong> {{ number_format($order->total_price, 0, ',', '.') }} VND
                            </p>
                            <p><strong>Payment Method:</strong> {{ $order->payment_method->value === 'cod' ? 'Cash on Delivery (COD)' : 'Bank Transfer' }}</p>
                            <p><strong>Status:</strong> <span
                                    class="order-status status-{{ Str::slug($order->status->value) }}">{{ ucfirst($order->status->value) }}</span>
                            </p>
                            @if ($order->discount_code)
                                <p><strong>Discount Applied:</strong> {{ $order->discount_code }}
                                    ({{ number_format($order->discount_value, 0, ',', '.') }} VND)
                                </p>
                            @endif
                        </div>
                        <div class="order-footer">
                            <a href="{{ route('orders.detail', $order->id) }}" class="btn-view-details">View
                                Details</a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="pagination-wrapper">
                <div class="pagination-container">
                    {{ $orders->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
