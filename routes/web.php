<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login.show'));

// Guest-only (not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('pages.login'))->name('login.show');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register.show');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Logout (logged in)
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Auth-only pages (must be logged in)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('pages.dashboard'))->name('dashboard');

    Route::get('/tickets', fn () => view('pages.tickets'))->name('tickets');
    Route::get('/ticket', fn () => view('pages.ticket'))->name('ticket');
    Route::get('/create-ticket', fn () => view('pages.create-ticket'))->name('create-ticket');

    Route::get('/knowledge', fn () => view('pages.knowledge'))->name('knowledge');
    Route::get('/knowledge-article', fn () => view('pages.knowledge-article'))->name('knowledge-article');

    Route::get('/contact', fn () => view('pages.contact'))->name('contact');
    Route::get('/profile', fn () => view('pages.profile'))->name('profile');
    Route::get('/settings', fn () => view('pages.settings'))->name('settings');
});