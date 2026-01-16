<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route; // Route lets us define website URLs like /login, /register, etc.

/*
ROUTES = rules:
"If the browser requests METHOD + URL, do something."

Route::<METHOD>('<URL>', <WHAT_TO_RUN>)->name('<ROUTE_NAME>');
- <METHOD> is usually get or post
- <URL> is what you type in the browser (like /dashboard or /login)
- <WHAT_TO_RUN> is either:
    - a view (quick page), or
    - a controller method (when logic is needed)
- ->name(...) is optional but recommended, it gives the route a nickname for easy linking.

METHOD meanings:
- GET  = show a page
- POST = submit a form / perform an action (login, register, logout)

Middleware = a gatekeeper:
- guest = only NOT-logged-in users can access
- auth  = only logged-in users can access
*/

// If someone goes to the homepage "/", send them to the login page.
Route::get('/', fn () => redirect()->route('login'));

// ---------------------------
// GUEST ROUTES (NOT logged in)
// ---------------------------
Route::middleware('guest')->group(function () {
    // Show the login page (HTML).
    // Visiting /login in the browser runs this.
    Route::get('/login', fn () => view('pages.login'))->name('login');
    // When the login form is submitted, it sends POST /login.
    // This runs AuthController@login (checks email/password, logs in).
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Show the register page (HTML).
    //Route::get('/register', [AuthController::class, 'showRegister'])->name('register.show');
    Route::get('/register', fn () => view('pages.register'))->name('register');
    // When the register form is submitted, it sends POST /register.
    // This runs AuthController@register (creates user, logs in).
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// ---------------------------
// LOGOUT (must be logged in)
// ---------------------------
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---------------------------
// AUTH ROUTES (logged in)
// ---------------------------
Route::middleware('auth')->group(function () {
    // // If you’re logged in, you can see these pages.
    // If you’re NOT logged in, Laravel redirects you to /login.
    Route::get('/dashboard', fn () => view('pages.dashboard'))->name('dashboard');
 
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/ticket', fn () => redirect()->route('tickets.index'))->name('ticket');
    Route::get('/create-ticket', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::post('/tickets/{ticket}/messages', [TicketController::class, 'storeMessage'])->name('tickets.messages.store');
    Route::post('/tickets/{ticket}/attachments', [TicketController::class, 'uploadAttachments'])->name('tickets.attachments.store');
    Route::get('/tickets/{ticket}/attachments/view', [TicketController::class, 'viewAttachment'])->name('tickets.attachments.view');
    Route::get('/tickets/{ticket}/files/{file}', [TicketController::class, 'viewFile'])->whereNumber('file')->name('tickets.files.show');
    Route::get('/tickets/{ticket}/message-files/{file}', [TicketController::class, 'viewMessageFile'])->whereNumber('file')->name('tickets.messageFiles.show');

    Route::middleware('role:admin,it')->group(function () {
        Route::post('/tickets/{ticket}/assign-to-me', [TicketController::class, 'assignToMe'])->name('tickets.assignToMe');
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus');
        Route::post('/tickets/{ticket}/tags', [TicketController::class, 'addTag'])->name('tickets.tags.store');
        Route::delete('/tickets/{ticket}/tags', [TicketController::class, 'removeTag'])->name('tickets.tags.delete');

        Route::delete('/tickets/{ticket}/attachments', [TicketController::class, 'deleteLegacyAttachment'])->name('tickets.attachments.delete');
        Route::delete('/tickets/{ticket}/files/{file}', [TicketController::class, 'deleteFile'])->whereNumber('file')->name('tickets.files.delete');
        Route::delete('/tickets/{ticket}/message-files/{file}', [TicketController::class, 'deleteMessageFile'])->whereNumber('file')->name('tickets.messageFiles.delete');
    });

    Route::get('/knowledge', fn () => view('pages.knowledge'))->name('knowledge');
    Route::get('/knowledge-article', fn () => view('pages.knowledge-article'))->name('knowledge-article');

    Route::get('/contact', fn () => view('pages.contact'))->name('contact');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/settings', fn () => view('pages.settings'))->name('settings');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->whereNumber('user')->name('users.update');
    });
});