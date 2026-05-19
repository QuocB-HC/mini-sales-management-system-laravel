@extends('layouts.admin')

@section('title', 'Products Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/products/index.css') }}">
@endpush

@section('content')
    <div class="main-container">
        <header>
            <h1>Products Management</h1>

            <div class="search-container">
                <form action="{{ route('admin.products.search', $shop->id) }}" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Enter product name or sku"
                        value="{{ request('search') }}">
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div>

            <button type="button" onclick="openModal('customModal')" class="view-btn btn-add">
                Open Pending Products Modal
            </button>
        </header>

        <!-- Category Filter Tabs -->
        <div class="category-filters">
            <a href="{{ route('admin.products.index', $shop->id) }}"
                class="view-btn filter-btn {{ !request('category_id') ? 'active' : '' }}">
                All
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('admin.products.index', ['shop_id' => $shop->id, 'category_id' => $cat->id]) }}"
                    class="view-btn filter-btn {{ request('category_id') == $cat->id ? 'active' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>

        <section class="recent-section">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>#{{ $product->id }}</td>
                            <td>
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-img">
                            </td>
                            <td><small>{{ $product->sku }}</small></td>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td>{{ number_format($product->price, 0, ',', '.') }} VND</td>
                            <td>{{ $product->stock_quantity }}</td>
                            <td>
                                <span
                                    class="status {{ $product->status->value }}">{{ ucfirst(str_replace('_', ' ', $product->status->value)) }}</span>
                            </td>
                            <td class="action-btns">
                                @php
                                    $isRejected = $product->isRejected();
                                    $isHidden = $product->isHidden();
                                @endphp

                                @if ($isRejected)
                                    <form
                                        onsubmit="confirmModal(event, 'Make Product Approved Confirm', 'Are you sure you want to make this product approved?')"
                                        action="{{ route('admin.products.approve', $product->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-icon btn-approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @else
                                    <form
                                        onsubmit="confirmModal(event, 'Make Product Rejected Confirm', 'Are you sure you want to make this product rejected?')"
                                        action="{{ route('admin.products.reject', $product->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-icon btn-reject">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                @endif

                                @if ($isHidden)
                                    <form
                                        onsubmit="confirmModal(event, 'Make Product Visible Confirm', 'Are you sure you want to make this product visible?')"
                                        action="{{ route('admin.products.visible', $product->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-icon btn-hide">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </form>
                                @else
                                    @php
                                        $canHide = $product->canBeHidden();
                                        $cannotHide = !$canHide;
                                    @endphp

                                    <form
                                        onsubmit="confirmModal(event, 'Make Product Hidden Confirm', 'Are you sure you want to make this product hidden?')"
                                        action="{{ route('admin.products.hide', $product->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="btn-icon btn-hide {{ $cannotHide ? 'disabled' : '' }}"
                                            @disabled($cannotHide)>
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="no-data">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div class="pagination-wrapper">
            <div class="pagination-container">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <div id="customModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Pending Products</h2>
                <button class="close-icon" onclick="closeModal('customModal')">&times;</button>
            </div>
            <div class="modal-body">
                <section class="recent-section">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingProducts as $product)
                                <tr>
                                    <td>#{{ $product->id }}</td>
                                    <td>
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                            class="product-img">
                                    </td>
                                    <td>{{ $product->sku }}</td>
                                    <td><strong>{{ $product->name }}</strong></td>
                                    <td>{{ number_format($product->price, 0, ',', '.') }} VND</td>
                                    <td>{{ $product->stock_quantity }}</td>
                                    <td>
                                        <span
                                            class="status {{ $product->status->value }}">{{ ucfirst(str_replace('_', ' ', $product->status->value)) }}</span>
                                    </td>
                                    <td class="action-btns">
                                        <form
                                            onsubmit="confirmModal(event, 'Approve Confirm', 'Are you sure you want to approve this product?')"
                                            action="{{ route('admin.products.approve', $product->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="view-btn btn-add">
                                                Approve
                                            </button>
                                        </form>
                                        <form
                                            onsubmit="confirmModal(event, 'Reject Confirm', 'Are you sure you want to reject this product?')"
                                            action="{{ route('admin.products.reject', $product->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="view-btn btn-delete">
                                                Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="no-data">No products found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </section>
            </div>
        </div>
    </div>

    <x-modal-custom />
@endsection

@push('scripts')
    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
    </script>
@endpush
