@extends('layouts.user', ['hideHeaderFooter' => true])

@section('title', 'Create Shop')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/seller/shops/create.css') }}">
@endpush

@section('content')
    <div class="form-container">
        <h2>Setup Your Shop</h2>
        <form onsubmit="confirmModal(event, 'Create Shop Confirm', 'Are you sure you want to create this shop?')"
            action="{{ route('shop.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="name">Shop Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    placeholder="Enter shop name">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="{{ old('address') }}" required
                    placeholder="Shop address">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                    placeholder="Contact number">
            </div>

            <div class="social-links">
                <p>Social Media (Optional)</p>
                <input type="text" name="facebook_url" value="{{ old('facebook_url') }}" placeholder="Facebook URL">
                <input type="text" name="instagram_url" value="{{ old('instagram_url') }}" placeholder="Instagram URL">
                <input type="text" name="twitter_url" value="{{ old('twitter_url') }}" placeholder="Twitter URL">
            </div>

            <button type="submit" class="btn-submit">Create Shop</button>
        </form>
    </div>
@endsection
