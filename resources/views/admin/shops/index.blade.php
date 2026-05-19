@extends('layouts.admin')

@section('title', 'Shops Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/shops/index.css') }}">
@endpush

@section('content')
    <div class="main-container">
        <header>
            <h1>Shops Management</h1>

            <div class="search-container">
                <form action="{{ route('admin.shops.search') }}" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Enter shop name or owner email"
                        value="{{ request('search') }}">
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div>

            <button type="button" onclick="openModal('customModal')" class="view-btn btn-add">
                Open Pending Shops Modal
            </button>
        </header>

        <section class="recent-section">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Logo</th>
                        <th>User Name</th>
                        <th>Shop Name</th>
                        <th>Address</th>
                        <th>Phone Number</th>
                        <th>Social Media</th>
                        <th>Status</th>
                        <th>Products</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shops as $shop)
                        <tr>
                            <td>#{{ $shop->id }}</td>
                            <td>
                                <img src="{{ $shop->logo_url }}" alt="{{ $shop->name }}" class="product-img">
                            </td>
                            <td><small>{{ $shop->user->name }}</small></td>
                            <td><strong>{{ $shop->name }}</strong></td>
                            <td>{{ $shop->address }}</td>
                            <td>{{ $shop->phone }}</td>
                            <td>
                                @php
                                    $platforms = [
                                        'facebook' => 'fab fa-facebook',
                                        'instagram' => 'fab fa-instagram',
                                        'twitter' => 'fab fa-twitter',
                                    ];
                                    $hasSocial = false;
                                @endphp

                                @foreach ($platforms as $key => $icon)
                                    @if ($url = $shop->{$key . '_url'})
                                        <a href="{{ $url }}" target="_blank" class="social-link">
                                            <i class="{{ $icon }}"></i>
                                        </a>
                                        @php $hasSocial = true; @endphp
                                    @endif
                                @endforeach

                                @if (!$hasSocial)
                                    <span class="no-data">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span
                                    class="status {{ $shop->status->value }}">{{ ucfirst(str_replace('_', ' ', $shop->status->value)) }}</span>
                            </td>
                            <td class="action-btns">
                                <a href="{{ route('admin.products.index', $shop->id) }}" class="view-btn btn-edit">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="no-data">No shops found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div class="pagination-wrapper">
            <div class="pagination-container">
                {{ $shops->links() }}
            </div>
        </div>
    </div>

    <div id="customModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Pending Shops</h2>
                <button class="close-icon" onclick="closeModal('customModal')">&times;</button>
            </div>
            <div class="modal-body">
                <section class="recent-section">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Logo</th>
                                <th>User Name</th>
                                <th>Shop Name</th>
                                <th>Address</th>
                                <th>Phone Number</th>
                                <th>Social Media</th>
                                <th>Status</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingShops as $shop)
                                <tr>
                                    <td>#{{ $shop->id }}</td>
                                    <td>
                                        <img src="{{ $shop->logo_url }}" alt="{{ $shop->name }}" class="product-img">
                                    </td>
                                    <td><small>{{ $shop->user->name }}</small></td>
                                    <td><strong>{{ $shop->name }}</strong></td>
                                    <td>{{ $shop->address }}</td>
                                    <td>{{ $shop->phone }}</td>
                                    <td>
                                        @php
                                            $platforms = [
                                                'facebook' => 'fab fa-facebook',
                                                'instagram' => 'fab fa-instagram',
                                                'twitter' => 'fab fa-twitter',
                                            ];
                                            $hasSocial = false;
                                        @endphp

                                        @foreach ($platforms as $key => $icon)
                                            @if ($url = $shop->{$key . '_url'})
                                                <a href="{{ $url }}" target="_blank" class="social-link">
                                                    <i class="{{ $icon }}"></i>
                                                </a>
                                                @php $hasSocial = true; @endphp
                                            @endif
                                        @endforeach

                                        @if (!$hasSocial)
                                            <span class="no-data">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="status {{ $shop->status->value }}">{{ ucfirst(str_replace('_', ' ', $shop->status->value)) }}</span>
                                    </td>
                                    <td class="action-btns">
                                        <form
                                            onsubmit="confirmModal(event, 'Approve Confirm', 'Are you sure you want to approve this shop?')"
                                            action="{{ route('admin.shops.approve', $shop->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="view-btn btn-add">Approve</button>
                                        </form>
                                        <form
                                            onsubmit="confirmModal(event, 'Reject Confirm', 'Are you sure you want to reject this shop?')"
                                            action="{{ route('admin.shops.reject', $shop->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="view-btn btn-delete">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="no-data">No shops found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </section>

                <div class="pagination-wrapper">
                    <div class="pagination-container">
                        {{ $pendingShops->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
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
