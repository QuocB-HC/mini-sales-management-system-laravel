@extends('layouts.user', ['hideHeaderFooter' => true])

@section('title', 'Profile Edit')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/profile-edit.css') }}">
@endpush

@section('content')
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <h2>Edit Your Profile</h2>
            </div>

            <form onsubmit="confirmModal(event, 'Edit Profile', 'Are you sure to confirm the edit?')"
                action="{{ route('profile.update') }}" method="POST" class="profile-body">
                @csrf
                @method('PUT')

                <div class="info-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        placeholder="Enter your full name">
                </div>

                <div class="info-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                        placeholder="Enter your phone">
                </div>

                <div class="info-group">
                    <label>Address</label>
                    <textarea name="address" rows="3" placeholder="Enter your address">{{ old('address', $user->address) }}</textarea>
                </div>

                <div class="profile-actions">
                    <button type="submit" class="btn-edit">Save Changes</button>
                    <a href="{{ route('profile.index') }}" class="btn-logout-alt"
                        style="text-decoration:none; text-align:center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
