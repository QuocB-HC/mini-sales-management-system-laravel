<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiscountController as AdminDiscountController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\ShopController as SellerShopController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// 1. PUBLIC ROUTES (accessible to all users)
// Home page route
Route::get('/', [ProductController::class, 'homePage'])->name('home');

// Product routes
Route::prefix('products')->as('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index'); // products.index
    Route::get('/category/{category}', [ProductController::class, 'showByCategory'])->name('byCategory'); // products.byCategory
    Route::get('/detail/{id}', [ProductController::class, 'detail'])->name('detail'); // products.detail
    Route::get('/search', [ProductController::class, 'search'])->name('search'); // products.search
    Route::get('/search-ajax', [ProductController::class, 'searchAjax'])->name('searchAjax'); // products.searchAjax
});

Route::get('/shop/{id}', [ShopController::class, 'index'])->name('shop.index'); // shop.index

// Cart routes
Route::prefix('cart')->as('cart.')->group(function () {
    Route::post('/add/{id}', [CartController::class, 'addToCart'])->name('add'); // cart.add
    Route::get('/', [CartController::class, 'index'])->name('index'); // cart.index
    Route::post('/update/{id}', [CartController::class, 'updateQuantity'])->name('update'); // cart.update
    Route::delete('/remove/{id}', [CartController::class, 'removeFromCart'])->name('remove'); // cart.remove
    Route::delete('/clear', [CartController::class, 'clearCart'])->name('clear'); // cart.clear
});

// 2. AUTHENTICATION ROUTES
// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/send-code', [AuthController::class, 'sendVerificationCode'])->name('send.code');
Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify.email');
Route::get('/register-complete', [AuthController::class, 'showCompleteRegister'])->name('register.complete');
Route::get('/forget-password', [AuthController::class, 'showForgetPassword'])->name('forget.password');

Route::prefix('password')->as('password.')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOtp'])->name('sendOtp'); // password.sendOtp
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verifyOtp'); // password.verifyOtp
    Route::post('/update', [AuthController::class, 'updatePassword'])->name('update'); // password.update
});

// 3. PROTECTED ROUTES (only for authenticated users)
Route::middleware('auth')->group(function () {
    // ==================== USER PROFILE ROUTES ==================== //
    // User profile routes
    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/', [UserController::class, 'show'])->name('index'); // profile.index
        Route::get('/edit', [UserController::class, 'edit'])->name('edit'); // profile.edit
        Route::put('/update', [UserController::class, 'update'])->name('update'); // profile.update
        Route::put('/avatar', [UserController::class, 'updateAvatar'])->name('avatar'); // profile.avatar
    });

    // Checkout routes
    Route::prefix('checkout')->as('checkout.')->group(function () {
        Route::get('/', [CartController::class, 'checkout'])->name('index'); // checkout.index
        Route::post('/place-order', [CartController::class, 'placeOrder'])->name('placeOrder'); // checkout.placeOrder
        Route::post('/apply-discount', [CartController::class, 'applyDiscount'])->name('applyDiscount');
        Route::get('/success/{id}', [CartController::class, 'orderSuccess'])->name('success'); // checkout.success
        Route::get('/vnpay-return', [CartController::class, 'vnpayReturn'])->name('vnpayReturn'); // checkout.returnVnpay
    });

    // Orders routes
    Route::prefix('orders')->as('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index'); // orders.index
        Route::get('/{id}', [OrderController::class, 'show'])->name('detail'); // orders.detail
    });

    // Shop information routes
    Route::prefix('shop')->as('shop.')->group(function () {
        Route::get('/create', [ShopController::class, 'create'])->name('create'); // shop.create
        Route::post('/store', [ShopController::class, 'store'])->name('store'); // shop.store
    });

    // VNPAY return route
    Route::get('/return-vnpay', [CartController::class, 'vnpayReturn']);

    // ==================== SELLER ROUTES ==================== //
    Route::middleware('can:seller')->prefix('seller')->as('seller.')->group(function () {
        // Shop information routes
        Route::prefix('shop')->as('shop.')->group(function () {
            Route::get('/information/{id?}', [SellerShopController::class, 'index'])->name('index'); // seller.shop.index
        });

        // Product management routes
        Route::prefix('products')->as('products.')->group(function () {
            Route::get('/{shopId?}/create', [SellerProductController::class, 'create'])->name('create'); // seller.products.create
            Route::post('/store', [SellerProductController::class, 'store'])->name('store'); // seller.products.store
            Route::get('/edit/{id}', [SellerProductController::class, 'edit'])->name('edit'); // seller.products.edit
            Route::put('/update/{id}', [SellerProductController::class, 'update'])->name('update'); // seller.products.update
            Route::patch('/update-status-to-hidden/{product}', [SellerProductController::class, 'updateStatusToHidden'])->name('updateStatusToHidden'); // seller.products.updateStatusToHidden
            Route::patch('/update-status-to-visible/{product}', [SellerProductController::class, 'updateStatusToVisible'])->name('updateStatusToVisible'); // seller.products.updateStatusToVisible
            Route::get('/{shopId?}', [SellerProductController::class, 'index'])->name('index'); // seller.products.index
        });
    });

    // ==================== ADMIN ROUTES ==================== //
    Route::middleware('can:admin')->prefix('admin')->as('admin.')->group(function () {
        // Admin dashboard route
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard'); // admin.dashboard

        // Admin shop routes
        Route::prefix('shops')->as('shops.')->group(function () {
            Route::get('/', [AdminShopController::class, 'index'])->name('index'); // admin.shops.index
            Route::get('/search', [AdminShopController::class, 'search'])->name('search'); // admin.shops.search
            Route::put('/{shop}/approve', [AdminShopController::class, 'updateStatusToApproved'])->name('approve'); // admin.shops.approve
            Route::put('/{shop}/reject', [AdminShopController::class, 'updateStatusToRejected'])->name('reject'); // admin.shops.reject
        });

        // Get products by shop
        Route::prefix('shops')->as('products.')->group(function () {
            Route::get('/{shop_id}/products', [AdminProductController::class, 'index'])->name('index'); // admin.products.index
            Route::get('/{shop_id}/products/search', [AdminProductController::class, 'search'])->name('search'); // admin.products.search
        });

        // Admin product routes
        Route::prefix('products')->as('products.')->group(function () {
            Route::patch('/{product}/approve', [AdminProductController::class, 'updateStatusToApproved'])->name('approve'); // admin.products.approve
            Route::patch('/{product}/reject', [AdminProductController::class, 'updateStatusToRejected'])->name('reject'); // admin.products.reject
            Route::patch('/{product}/hide', [AdminProductController::class, 'updateStatusToHidden'])->name('hide'); // admin.products.hide
            Route::patch('/{product}/visible', [AdminProductController::class, 'updateStatusToVisible'])->name('visible'); // admin.products.visible
        });

        // Admin category routes
        Route::resource('categories', AdminCategoryController::class)->except(['show']); // admin.categories.index, admin.categories.create, etc.

        // Admin user routes
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index'); // admin.users.index
        Route::put('/users/{user}', [AdminUserController::class, 'updateIsBanned'])->name('users.updateIsBanned'); // admin.users.updateIsBanned
        Route::get('/users/search', [AdminUserController::class, 'search'])->name('users.search'); // admin.users.search

        // Admin order routes
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index'); // admin.orders.index
        Route::put('/orders/{order}', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus'); // admin.orders.updateStatus

        // Admin discount routes
        Route::resource('discounts', AdminDiscountController::class)->except(['show']); // admin.discounts.index, admin.discounts.create, etc.
    });
});
