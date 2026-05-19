@php
    use App\Enums\OrderStatus;
@endphp

@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush

@section('content')
    <div class="main-container">
        <header>
            <h1>Overview</h1>
            <div class="user-info">
                <span>Welcome, <strong>{{ auth()->user()->name }}</strong></span>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
                <div class="stat-info">
                    <h3>Total Revenue</h3>
                    <p>{{ number_format($totalRevenue, 0, ',', '.') }} VND</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pink"><i class="fa-solid fa-bag-shopping"></i></div>
                <div class="stat-info">
                    <h3>Total Orders</h3>
                    <p>{{ number_format($totalOrders, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-user"></i></div>
                <div class="stat-info">
                    <h3>New Customers / Total (Month)</h3>
                    <p>{{ number_format($newUsersThisMonth, 0, ',', '.') }} /
                        {{ number_format($totalUsers, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="charts-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="chart-container">
                <h3>Revenue Last 7 Days</h3>
                <canvas id="revenueChart"></canvas>
            </div>

            <div class="chart-container">
                <h3>Order Status Distribution</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <section class="recent-section">
            <h2>Orders to Process</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingOrders as $order)
                        <tr>
                            <td data-label="Order ID">#{{ $order->id }}</td>
                            <td data-label="Customer">{{ $order->receiver_name ?? 'Guest' }}</td>
                            <td data-label="Status"><span
                                    class="status {{ $order->status->value }}">{{ ucfirst($order->status->value) }}</span>
                            </td>
                            <td data-label="Total">{{ number_format($order->total_price, 0, ',', '.') }} VND</td>
                            <td data-label="Actions">
                                @if ($order->status->value !== 'cancelled')
                                    <form
                                        onsubmit="confirmModal(event, 'Change Order Status', 'Are you sure to change status of this order?')"
                                        action="{{ route('admin.orders.updateStatus', $order) }}" method="POST"
                                        class="action-btns">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="status-select"
                                            data-original="{{ $order->status->value }}">
                                            @foreach (OrderStatus::cases() as $status)
                                                <option value="{{ $status->value }}"
                                                    {{ $order->status->value === $status->value ? 'selected' : '' }}>
                                                    {{ ucfirst($status->value) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="update-button" disabled>Update</button>
                                    </form>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center;">No active orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="pagination-wrapper">
                <div class="pagination-container">
                    {{ $pendingOrders->links() }}
                </div>
            </div>
        </section>

        <section class="recent-section">
            <h2>Shipping Orders</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shippingOrders as $order)
                        <tr>
                            <td data-label="Order ID">#{{ $order->id }}</td>
                            <td data-label="Customer">{{ $order->receiver_name ?? 'Guest' }}</td>
                            <td data-label="Status"><span
                                    class="status shipping">{{ ucfirst($order->status->value) }}</span></td>
                            <td data-label="Total">{{ number_format($order->total_price, 0, ',', '.') }} VND</td>
                            <td data-label="Actions">
                                @if ($order->status->value !== 'cancelled')
                                    <form
                                        onsubmit="confirmModal(event, 'Change Order Status', 'Are you sure to change status of this order?')"
                                        action="{{ route('admin.orders.updateStatus', $order) }}" method="POST"
                                        class="update-status-form">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="status-select"
                                            data-original="{{ $order->status->value }}">
                                            @foreach (OrderStatus::cases() as $status)
                                                <option value="{{ $status->value }}"
                                                    {{ $order->status->value === $status->value ? 'selected' : '' }}>
                                                    {{ ucfirst($status->value) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="update-button" disabled>Update</button>
                                    </form>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center;">No active orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="pagination-wrapper">
                <div class="pagination-container">
                    {{ $shippingOrders->links() }}
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        //=========== Revenue Last 7 Days ===========//
        const ctx = document.getElementById('revenueChart').getContext('2d');

        // Transfer data from PHP to JS
        const labels = {!! json_encode($revenueLast7Days->pluck('date')) !!};
        const data = {!! json_encode($revenueLast7Days->pluck('total')) !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (VND)',
                    data: data,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            }
        });

        //=========== Order Status Stats ===========//
        const statusCtx = document.getElementById('statusChart').getContext('2d');

        // Transfer data from PHP to JS
        const statusLabels = {!! json_encode($orderStatusStats->pluck('status')) !!};
        const statusData = {!! json_encode($orderStatusStats->pluck('count')) !!};

        // The function automatically assigns a color based on the state name
        const getColor = (status) => {
            switch (status) {
                case 'completed':
                    return '#28a745'; // Green
                case 'pending':
                    return '#ffc107'; // Yellow
                case 'processing':
                    return '#17a2b8'; // Ocean blue
                case 'shipping':
                    return '#007bff'; // Heavy blue
                case 'cancelled':
                    return '#dc3545'; // Red
                default:
                    return '#6c757d'; // Gray
            }
        };

        const backgroundColors = statusLabels.map(label => getColor(label));

        new Chart(statusCtx, {
            type: 'doughnut', // Can change to 'pie' or 'bar'
            data: {
                labels: statusLabels.map(label => label.toUpperCase()),
                datasets: [{
                    data: statusData,
                    backgroundColor: backgroundColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom', // Put note to the bottom
                    }
                }
            }
        });

        //=========== Order Status Update Logic ===========//
        document.querySelectorAll('.status-select').forEach(select => {
            const btn = select.closest('form').querySelector('.update-button');
            const originalStatus = select.dataset.original;

            select.addEventListener('change', () => {
                btn.disabled = select.value === originalStatus;
            });
        });
    </script>
@endpush
