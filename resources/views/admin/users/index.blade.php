@extends('layouts.admin')

@section('title', 'Users Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/users/index.css') }}">
@endpush

@section('content')
    <div class="main-container">
        <main class="main-content">
            <header>
                <h1>Users Management</h1>

                <div class="search-container">
                    <form action="{{ route('admin.users.search') }}" method="GET" class="search-form">
                        <input type="text" name="search" placeholder="Enter user email" value="{{ request('search') }}">
                        <button type="submit" class="search-btn">Search</button>
                    </form>
                </div>
            </header>

            <section class="recent-section">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Joined Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>#{{ $user->id }}</td>
                                <td class="text-center"><strong>{{ $user->name }}</strong></td>
                                <td class="text-center">{{ $user->role->name }}</td>
                                <td class="text-center text-overflow">{{ $user->email }}</td>
                                <td class="text-center">{{ $user->phone }}</td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <span class="status {{ $user->is_banned ? 'banned' : 'active' }}">
                                        {{ $user->is_banned ? 'Banned' : 'Active' }}
                                    </span>
                                </td>
                                <td class="action-btns">
                                    <form
                                        onsubmit="confirmModal(event, 'Ban User', 'Are you sure you want to {{ $user->is_banned ? 'unban' : 'ban' }} this user?', 'delete')"
                                        action="{{ route('admin.users.updateIsBanned', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="is_banned" value="{{ $user->is_banned ? 0 : 1 }}">
                                        <button type="submit"
                                            class="view-btn {{ $user->is_banned ? 'btn-add' : 'btn-delete' }}">
                                            {{ $user->is_banned ? 'Unban User' : 'Ban User' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="no-data">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="pagination-wrapper">
                    <div class="pagination-container">
                        {{ $users->links() }}
                    </div>
                </div>
            </section>
        </main>
    </div>
@endsection
