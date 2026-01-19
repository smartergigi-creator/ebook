<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\EbookShareController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Login Required)
|--------------------------------------------------------------------------
*/

// Home page (listing / landing)
Route::get('/', [EbookController::class, 'index'])
    ->name('ebooks.index');

// Public ebook view (optional preview)
Route::get('/ebook/public/{id}', [EbookController::class, 'publicView'])
    ->name('ebook.public');

// Public share link (token based)
Route::get('/share/{token}', [EbookShareController::class, 'view'])
    ->name('ebook.share.view');
// Generate share link
    Route::get('/ebook/share/{id}', [EbookShareController::class, 'generate'])
        ->name('ebook.share');

/*
|--------------------------------------------------------------------------
| Protected Routes (JWT Authentication Required)
|--------------------------------------------------------------------------
*/

// Route::middleware(['jwt.auth'])->group(function () {

    // Upload ebook
    Route::post('/ebooks/upload', [EbookController::class, 'store'])
        ->name('ebooks.store');

    // View ebook (logged-in users only)
    Route::get('/ebook/view/{id}', [EbookController::class, 'view'])
        ->name('ebook.view');

    // Delete ebook
    Route::delete('/ebook/delete/{id}', [EbookController::class, 'delete'])
        ->name('ebook.delete');

    
// });
