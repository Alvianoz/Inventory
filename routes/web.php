<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\AdminLoanController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SegmentController;

// ... (Route Login & Register) ...
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Rute yang memerlukan login (dilindungi middleware)
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- RUTE UNTUK SEMUA ROLE (Anggota, Kasir, Pengelola) ---
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/{id}', [HistoryController::class, 'show'])->name('history.show');
    Route::delete('/history/{id}', [HistoryController::class, 'destroy'])->name('history.destroy');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');

    // --- RUTE UNTUK SEMUA USER (Peminjaman) ---
    Route::get('/borrow', [LoanController::class, 'borrow'])->name('loan.borrow');
    Route::post('/borrow/submit', [LoanController::class, 'submitBorrow'])->name('loan.submit');
    Route::get('/return', [LoanController::class, 'return'])->name('loan.return');
    Route::post('/return/submit', [LoanController::class, 'submitReturn'])->name('loan.return.submit');

    // --- RUTE HANYA UNTUK KASIR & PENGELOLA ---
    Route::middleware(['role:kasir,pengelola'])->group(function () {
        // Product Management Routes
        Route::get('/manage', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::put('/products/{product}/restock', [ProductController::class, 'restock'])->name('products.restock');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        
        // QR Code Routes
        Route::get('/products/{id}/qr', [ProductController::class, 'showQR'])->name('products.qr.show');

        // Admin Loan Management Routes
        Route::get('/admin/loans', [AdminLoanController::class, 'index'])->name('admin.loans');
        Route::post('/admin/loans/{id}/approve', [AdminLoanController::class, 'approveBorrow'])->name('admin.loans.approve');
        Route::post('/admin/loans/{id}/reject', [AdminLoanController::class, 'rejectBorrow'])->name('admin.loans.reject');
        Route::post('/admin/loans/{id}/confirm-return', [AdminLoanController::class, 'confirmReturn'])->name('admin.loans.confirm-return');
    });

    // --- RUTE HANYA UNTUK PENGELOLA ---
    Route::middleware(['role:pengelola'])->group(function () {
        Route::get('/manage-users', [UserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}/update-role', [UserController::class, 'updateRole'])->name('users.update.role');

        // Segments CRUD (pengelola)
        Route::resource('segments', SegmentController::class)->except(['show']);

        // Segmen QR (pengelola)
        Route::get('/segments/{id}/qr', [SegmentController::class, 'showQR'])->name('segments.qr.show');
    });

    // Return per segment (all authenticated users can access, staff will see confirm actions)
    Route::get('/return/segment/{segment}', [SegmentController::class, 'returnPage'])->name('segments.return');
});

// API Routes untuk QR Scanner (tidak perlu auth karena diakses via AJAX)
Route::get('/api/product/{id}', [LoanController::class, 'getProductByQR']);
Route::get('/api/loan/{productId}', [LoanController::class, 'getLoanByQR']);
Route::get('/api/admin/loan/{productId}/{transactionId}', [AdminLoanController::class, 'getLoanTransactionByQR']);
// API untuk QR segmen (mem-return URL untuk redirect ke page pengembalian segmen)
Route::get('/api/segment/{id}', [SegmentController::class, 'getByQR']);
// API: cari transaksi returning untuk produk di segmen tertentu (dipakai saat scan di halaman segmen)
Route::get('/api/segment/{segmentId}/product/{productId}', [SegmentController::class, 'getReturningByProduct']);