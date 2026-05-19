@extends('layouts.user')

@section('title', 'Home Page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/home.css') }}">
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Elevate Your Lifestyle with Mini Store</h1>
            <p>Discover a collection of top-tier tech and fashion products at unbeatable prices.</p>
            <a href="{{ route('products.index') }}" class="btn-primary">Buy Now <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>

    <!-- Categories -->
    <section class="categories container">
        <div class="section-header">
            <h2>Categories</h2>
        </div>

        <div class="category-grid">
            @foreach ($categories as $category)
                <a href="{{ route('products.byCategory', $category->id) }}"class="category-item">
                    <span>{{ $category->name }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Featured Products -->
    <section id="shop" class="featured-products container">
        <div class="section-header">
            <h2>Newest Products</h2>
            <a href="{{ route('products.index') }}" class="view-all">View All</a>
        </div>
        <div class="product-grid">
            @foreach ($products as $product)
                <div class="product-card">
                    <div class="product-image">
                        <img src="{{ $product->image_url ?? 'https://via.placeholder.com/150' }}"
                            alt="{{ $product->name }}">
                    </div>
                    <div class="product-info">
                        <div class="product-top">
                            <span class="product-cat">{{ $product->category->name }}</span> <br />
                            <a href="{{ route('products.detail', $product->id) }}"
                                class="product-name">{{ $product->name }}</a>
                            <p class="product-desc">{{ $product->description }}</p>
                        </div>
                        <div class="product-bottom">
                            <span class="price">{{ number_format($product->price, 0, ',', '.') }} VND</span>

                            <form class="add-to-cart-form" action="{{ route('cart.add', $product->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="quantity" value="1">

                                <button type="submit" class="add-to-cart"
                                    {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const url = this.getAttribute('action');
                const formData = new FormData(this);
                const dataObject = {};
                formData.forEach((value, key) => dataObject[key] = value);

                fetch(url, {
                        method: 'POST',
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify(dataObject)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof showToast === "function") {
                                showToast('success', data.message);
                            } else {
                                alert(data.message);
                            }

                            const cartCountEl = document.getElementById('cart-count');
                            if (cartCountEl) {
                                // Update new value from Controller return
                                cartCountEl.innerText = data.cart_count;

                                // If value > 0 then show badge, hide otherwise
                                if (data.cart_count > 0) {
                                    cartCountEl.classList.remove('d-none');
                                } else {
                                    cartCountEl.classList.add('d-none');
                                }
                            }
                        } else {
                            if (typeof showToast === "function") {
                                showToast('error', data.message);
                            } else {
                                alert(data.message);
                            }
                        }
                    })
                    .catch(error => {
                        if (typeof showToast === "function") {
                            showToast("error", error.message);
                        } else {
                            alert(error.message);
                        }
                    });
            });
        });
    </script>
@endpush
