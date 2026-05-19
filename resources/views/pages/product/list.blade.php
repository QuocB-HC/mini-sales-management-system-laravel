@extends('layouts.user')

@section('title', 'Product List')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/product-list.css') }}">
@endpush

@section('content')
    <div class="main-container">
        <div class="category-slider-container">
            <button class="nav-btn prev" onclick="scrollSlider(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="category-list" id="categorySlider">
                @foreach ($categories as $category)
                    <a href="{{ route('products.byCategory', $category->id) }}" class="category-item">
                        {{ $category->name }}</a>
                @endforeach
            </div>

            <button class="nav-btn next" onclick="scrollSlider(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="product-grid">
            @foreach ($products as $product)
                <div class="product-card">
                    <div style="justify-content: space-between; display: flex; flex-direction: column; height: 100%;">
                        <img src="{{ $product->image_url ?? 'https://via.placeholder.com/150' }}" class="product-image"
                            alt="{{ $product->name }}">

                        <div class="product-top">
                            <a href="{{ route('products.detail', $product->id) }}"
                                class="product-name">{{ $product->name }}</a>
                            <p class="product-category">Category: {{ $product->category->name }}</p>
                        </div>

                        <div class="product-bottom">
                            <p class="product-stock" style="color: {{ $product->stock_quantity > 0 ? 'green' : 'red' }}">
                                Kho: {{ $product->stock_quantity }} | SKU: {{ $product->sku }}
                            </p>

                            <div class="product-price-container">
                                <p class="product-price">
                                    {{ number_format($product->price, 0, ',', '.') }} VND
                                </p>
                                <form class="add-to-cart-form" action="{{ route('cart.add', $product->id) }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">

                                    <button type="submit" class="btn-add-cart"
                                        {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pagination-wrapper">
            <div class="pagination-container">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function scrollSlider(direction) {
            const slider = document.getElementById('categorySlider');
            const scrollAmount = 360;

            if (direction === -1) {
                slider.scrollLeft -= scrollAmount;
            } else {
                slider.scrollLeft += scrollAmount;
            }
        }

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
