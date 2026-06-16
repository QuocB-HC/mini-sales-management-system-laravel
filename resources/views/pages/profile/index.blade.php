@extends('layouts.user')

@section('title', 'My Profile')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/profile/index.css') }}">
@endpush

@section('content')
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="{{ auth()->user()->avatar_url ?? '/default-avatar.png' }}" width="80">

                    <button type="button" onclick="openImageModal('customModal')" class="camera-icon">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                </div>
                <h2>{{ $user->name }}</h2>
                <span class="badge">Member since {{ $user->created_at->format('M Y') }}</span>
            </div>

            <div class="profile-body">
                <div class="info-group">
                    <label><i class="fa-regular fa-envelope"></i> Email</label>
                    <p>{{ $user->email }}</p>
                </div>

                <div class="info-group">
                    <label><i class="fa-regular fa-user"></i> Full Name</label>
                    <p>{{ $user->name }}</p>
                </div>

                <div class="info-group">
                    <label><i class="fa-solid fa-phone"></i> Phone Number</label>
                    <p>{{ $user->phone ?? 'Chưa cập nhật số điện thoại' }}</p>
                </div>

                <div class="info-group">
                    <label><i class="fa-solid fa-location-dot"></i> Address</label>
                    <p>{{ $user->address ?? 'Chưa có địa chỉ giao hàng' }}</p>
                </div>

                <div class="profile-actions">
                    <a href="{{ route('profile.edit') }}" class="btn-edit">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>

    <div id="customModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div></div>
                <button class="close-icon" onclick="closeImageModal('customModal')">&times;</button>
            </div>

            <div class="modal-body">
                <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="file" name="avatar" accept="image/*">

                    <button type="submit">Update avatar</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openImageModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeImageModal(modalId) {
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
