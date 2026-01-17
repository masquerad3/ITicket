<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\KnowledgeBaseAdminController;
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
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
 
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

    // Deletions: staff can delete anything; normal users can delete their own.
    Route::delete('/tickets/{ticket}/attachments', [TicketController::class, 'deleteLegacyAttachment'])->name('tickets.attachments.delete');
    Route::delete('/tickets/{ticket}/files/{file}', [TicketController::class, 'deleteFile'])->whereNumber('file')->name('tickets.files.delete');
    Route::delete('/tickets/{ticket}/message-files/{file}', [TicketController::class, 'deleteMessageFile'])->whereNumber('file')->name('tickets.messageFiles.delete');
    Route::delete('/tickets/{ticket}/messages/{message}', [TicketController::class, 'deleteMessage'])->whereNumber('message')->name('tickets.messages.delete');

    Route::middleware('role:admin,it')->group(function () {
        Route::post('/tickets/{ticket}/assign-to-me', [TicketController::class, 'assignToMe'])->name('tickets.assignToMe');
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus');
        Route::post('/tickets/{ticket}/tags', [TicketController::class, 'addTag'])->name('tickets.tags.store');
        Route::delete('/tickets/{ticket}/tags', [TicketController::class, 'removeTag'])->name('tickets.tags.delete');
    });

    Route::middleware('role:admin')->group(function () {
        // Knowledge Base management (admin only)
        Route::get('/knowledge/manage', [KnowledgeBaseAdminController::class, 'index'])->name('knowledge.manage');
        Route::get('/knowledge/manage/create', [KnowledgeBaseAdminController::class, 'create'])->name('knowledge.manage.create');
        Route::post('/knowledge/manage', [KnowledgeBaseAdminController::class, 'store'])->name('knowledge.manage.store');
        Route::get('/knowledge/manage/{article}/edit', [KnowledgeBaseAdminController::class, 'edit'])->whereNumber('article')->name('knowledge.manage.edit');
        Route::patch('/knowledge/manage/{article}', [KnowledgeBaseAdminController::class, 'update'])->whereNumber('article')->name('knowledge.manage.update');
        Route::patch('/knowledge/manage/{article}/publish', [KnowledgeBaseAdminController::class, 'setPublish'])->whereNumber('article')->name('knowledge.manage.publish');
        Route::patch('/knowledge/manage/{article}/featured', [KnowledgeBaseAdminController::class, 'setFeatured'])->whereNumber('article')->name('knowledge.manage.featured');
        Route::delete('/knowledge/manage/{article}', [KnowledgeBaseAdminController::class, 'destroy'])->whereNumber('article')->name('knowledge.manage.delete');

        // Ticket hard delete (admin only)
        Route::delete('/tickets/{ticket}/hard-delete', [TicketController::class, 'hardDelete'])->whereNumber('ticket')->name('tickets.hardDelete');
    });

    Route::get('/knowledge', [KnowledgeBaseController::class, 'index'])->name('knowledge');
    Route::get('/knowledge/{slug}', [KnowledgeBaseController::class, 'show'])->name('knowledge.show');

    // Back-compat with the old static article route.
    Route::get('/knowledge-article', fn () => redirect()->route('knowledge'))->name('knowledge-article');

    Route::get('/contact', fn () => view('pages.contact'))->name('contact');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/settings', fn () => view('pages.settings'))->name('settings');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->whereNumber('user')->name('users.update');
    });
});