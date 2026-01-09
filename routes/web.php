<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('pages.login'))->name('login');

Route::get('/dashboard', fn () => view('pages.dashboard'))->name('dashboard');
Route::get('/tickets', fn () => view('pages.tickets'))->name('tickets');
Route::get('/ticket', fn () => view('pages.ticket'))->name('ticket');
Route::get('/create-ticket', fn () => view('pages.create-ticket'))->name('create-ticket');

Route::get('/knowledge', fn () => view('pages.knowledge'))->name('knowledge');
Route::get('/knowledge-article', fn () => view('pages.knowledge-article'))->name('knowledge-article');

Route::get('/contact', fn () => view('pages.contact'))->name('contact');
Route::get('/profile', fn () => view('pages.profile'))->name('profile');
Route::get('/settings', fn () => view('pages.settings'))->name('settings');