<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront (public)
|--------------------------------------------------------------------------
*/
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Cart (Flow A) — works for guests and authenticated users.
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{variant}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{variant}', [CartController::class, 'remove'])->name('cart.remove');

// Compare (session-backed, up to 4).
Route::get('/compare', [CompareController::class, 'index'])->name('compare.index');
Route::post('/compare/{product}', [CompareController::class, 'add'])->name('compare.add');
Route::delete('/compare/{product}', [CompareController::class, 'remove'])->name('compare.remove');

// Buy Now (Flow B) — stores ephemeral session payload, bypasses cart.
Route::post('/buy-now', [CheckoutController::class, 'buyNow'])->name('checkout.buyNow');

// Checkout pages.
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [OrderController::class, 'place'])->name('checkout.place');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel/{order}', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// Payment callbacks.
Route::match(['get', 'post'], '/payment/callback', [OrderController::class, 'paymentCallback'])
    ->name('payment.callback');

// FakeGateway (local dev) simulated payment page.
Route::get('/checkout/fake/{order}', [CheckoutController::class, 'fake'])->name('checkout.fake');
Route::post('/checkout/fake/{order}', [OrderController::class, 'fakeDecision'])->name('checkout.fake.decision');

/*
|--------------------------------------------------------------------------
| Authenticated customer area
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Customer dashboard + order tracking (replaces Breeze's default /dashboard).
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/orders/{order}', [DashboardController::class, 'show'])->name('dashboard.order');

    // Wishlist.
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Profile (Breeze).
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin area (gated by the `admin` ability)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Product CRUD.
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::patch('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

    // Variant generation + inline management.
    Route::post('/products/{product}/variants/suggest', [ProductVariantController::class, 'suggest'])
        ->name('products.variants.suggest');
    Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])
        ->name('products.variants.store');
    Route::patch('/products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])
        ->name('products.variants.update');
    Route::delete('/products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])
        ->name('products.variants.destroy');

    // Attribute & value management.
    Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
    Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
    Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy'])->name('attributes.destroy');
    Route::post('/attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
    Route::delete('/attribute-values/{value}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');

    // Order management.
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])
        ->name('orders.updateStatus');
});

require __DIR__.'/auth.php';
