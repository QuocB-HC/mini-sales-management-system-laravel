@extends('layouts.user')

@section('title', $product->name . 'Detail')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/product-detail.css') }}">
@endpush

@section('content')
    <div class="product-container">
        <div class="product-main">
            <div class="product-gallery">
                <div class="main-image">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" id="currentImage">
                </div>
            </div>

            <div class="product-info">
                <nav class="breadcrumb">
                    <a href="/">Home</a> >
                    <a href="{{ route('products.byCategory', $product->category->id) }}">{{ $product->category->name }}</a>
                    >
                    <a>{{ $product->name }}</a>
                </nav>

                <h1 class="product-title">{{ $product->name }}</h1>

                <div class="product-meta">
                    <span class="sku">SKU: PRO-{{ $product->id }}</span>
                    <span class="stock {{ $product->stock_quantity > 0 ? 'in-stock' : 'out-of-stock' }}">
                        {{ $product->stock_quantity > 0 ? 'In-Stock' : 'Out-Of-Stock' }}
                    </span>
                </div>

                <div class="product-price">
                    <span class="current-price">{{ number_format($product->price, 0, ',', '.') }} VND</span>
                    {{-- <span class="old-price">1.200.000 VND</span> --}}
                </div>

                <form action="{{ route('cart.add', $product->id) }}" method="POST" class="add-to-cart-form">
                    @csrf
                    <div class="quantity-selector">
                        <button type="button" onclick="changeQty(-1)">-</button>
                        <input type="number" name="quantity" id="quantity" value="1" min="1"
                            max="{{ $product->stock_quantity }}" readonly>
                        <button type="button" onclick="changeQty(1)">+</button>
                    </div>

                    <button type="submit" class="btn-add-cart" {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                </form>

                <div class="product-trust-badges">
                    <div class="badge-item"><i class="fas fa-truck"></i> Nationwide delivery</div>
                    <div class="badge-item"><i class="fas fa-undo"></i> 7-day return policy</div>
                    <div class="badge-item"><i class="fas fa-shield-alt"></i> Manufacturer's warranty</div>
                </div>
            </div>
        </div>

        <div class="product-tabs">
            <div class="tab-header">
                Description
            </div>
            <div class="tab-content">
                {!! nl2br(e($product->description)) !!}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function changeQty(amount) {
            const qtyInput = document.getElementById('quantity');
            let currentQty = parseInt(qtyInput.value);
            let maxQty = parseInt(qtyInput.getAttribute('max'));

            currentQty += amount;
            if (currentQty < 1) currentQty = 1;
            if (currentQty > maxQty) currentQty = maxQty;

            qtyInput.value = currentQty;
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
