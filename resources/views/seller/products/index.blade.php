@php
    use App\Enums\ProductStatus;
@endphp

@extends('layouts.user')

@section('title', 'Products - ' . $currentShop->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/seller/products/index.css') }}">
@endpush

@section('content')
    <div class="main-container">
        <div class="header-wrapper">
            <div>
                <h1>Products of: {{ $currentShop->name }}</h1>
                <p>Manage your inventory and pricing.</p>
            </div>
            <a href="{{ route('seller.products.create', $currentShop->id) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>

        @if ($shops->count() > 1)
            <div class="shop-card">
                <label>Switch Shop:</label>
                <div class="shop-list">
                    @foreach ($shops as $item)
                        <a href="{{ route('seller.products.index', $item->id) }}"
                            class="shop-item {{ $item->id == $currentShop->id ? 'active' : '' }}">
                            {{ $item->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="text-center">Image</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">Category</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="text-center">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-img">
                            </td>
                            <td>
                                <div class="product-info">
                                    <div>
                                        <div class="product-name">{{ $product->name }}</div>
                                        <div class="product-sku">SKU: {{ $product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{{ $product->category->name }}</td>
                            <td class="text-center">{{ number_format($product->price, 0, ',', '.') }}đ</td>
                            <td class="text-center">{{ $product->stock_quantity }}</td>
                            <td class="text-center">
                                <span
                                    class="status {{ $product->status->value }}">{{ ucfirst(str_replace('_', ' ', $product->status->value)) }}</span>
                            </td>
                            <td class="content-center">
                                <div class="action-btns">
                                    <a href="{{ route('seller.products.edit', ['shopId' => $currentShop->id, 'id' => $product->id]) }}"
                                        class="btn-icon btn-edit"><i class="fas fa-edit"></i></a>

                                    @php
                                        $isHidden = $product->isHidden();
                                    @endphp

                                    @if ($isHidden)
                                        <form
                                            onsubmit="confirmModal(event, 'Make Product Visible Confirm', 'Are you sure you want to make this product visible?')"
                                            action="{{ route('seller.products.updateStatusToVisible', $product->id) }}"
                                            method="POST">
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
                                            action="{{ route('seller.products.updateStatusToHidden', $product->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="btn-icon btn-hide {{ $cannotHide ? 'disabled' : '' }}"
                                                @disabled($cannotHide)>
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem; color: #999;">
                                No products found in this shop.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="pagination-wrapper">
                <div class="pagination-container">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

    <x-modal-custom />
@endsection
