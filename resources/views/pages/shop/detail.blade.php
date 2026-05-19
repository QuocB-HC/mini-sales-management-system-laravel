@extends('layouts.user')

@section('title', $shop->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/shop/detail.css') }}">
@endpush

@section('content')
    <div class="shop-container">

        {{-- Shop Header --}}
        <div class="shop-header">
            <div class="shop-logo">
                @if ($shop->logo_url)
                    <img src="{{ $shop->logo_url }}" alt="{{ $shop->name }}">
                @else
                    <i class="fas fa-store"></i>
                @endif
            </div>

            <div class="shop-info">
                <h1 class="shop-name">{{ $shop->name }}</h1>
                <div class="shop-meta">
                    <span><i class="fas fa-map-marker-alt"></i> {{ $shop->address }}</span>
                    <span><i class="fas fa-phone"></i> {{ $shop->phone }}</span>
                </div>
                <div class="social-links">
                    @if ($shop->facebook_url)
                        <a href="{{ $shop->facebook_url }}" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    @endif
                    @if ($shop->instagram_url)
                        <a href="{{ $shop->instagram_url }}" target="_blank"><i class="fab fa-instagram"></i></a>
                    @endif
                    @if ($shop->twitter_url)
                        <a href="{{ $shop->twitter_url }}" target="_blank"><i class="fab fa-twitter"></i></a>
                    @endif
                </div>
            </div>

            <div class="shop-stats">
                <div class="stat-label">Product total</div>
                <div class="stat-value">{{ $products->total() }}</div>
            </div>
        </div>

        {{-- Products --}}
        <h2 class="section-title">Products</h2>

        <div class="products-grid">
            @forelse ($products as $product)
                <a href="{{ route('products.detail', $product->id) }}" class="product-card">
                    <div class="product-img">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                    </div>
                    <div class="product-info">
                        <div class="product-name">{{ $product->name }}</div>
                        <div class="product-price">{{ number_format($product->price, 0, ',', '.') }} VND</div>
                    </div>
                </a>
            @empty
                <p class="empty-text">Shop hasn't added any products yet.</p>
            @endforelse
        </div>

        <div class="pagination-wrapper">
            <div class="pagination-container">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection
