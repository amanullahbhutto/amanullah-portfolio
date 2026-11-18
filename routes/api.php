<?php

use App\Http\Controllers\Api\PortfolioApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Portfolio (React SPA Frontend)
|--------------------------------------------------------------------------
*/

Route::get('/portfolio', [PortfolioApiController::class, 'portfolio'])->name('api.portfolio');
Route::get('/services', [PortfolioApiController::class, 'services'])->name('api.services');
Route::get('/projects', [PortfolioApiController::class, 'projects'])->name('api.projects');
Route::get('/projects/{slug}', [PortfolioApiController::class, 'project'])->name('api.projects.show');
Route::get('/posts', [PortfolioApiController::class, 'posts'])->name('api.posts');
Route::get('/posts/{slug}', [PortfolioApiController::class, 'post'])->name('api.posts.show');
Route::post('/contact', [PortfolioApiController::class, 'submitContact'])->middleware('throttle:6,1')->name('api.contact');

/*
|--------------------------------------------------------------------------
| Admin API Endpoints (React Admin SPA)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    Route::get('/bootstrap', [\App\Http\Controllers\Api\AdminApiController::class, 'bootstrap'])->name('api.admin.bootstrap');
    Route::get('/dashboard', [\App\Http\Controllers\Api\AdminApiController::class, 'dashboard'])->name('api.admin.dashboard');
    Route::get('/content/{type}', [\App\Http\Controllers\Api\AdminApiController::class, 'getContent'])->name('api.admin.content.index');
    Route::post('/content/{type}', [\App\Http\Controllers\Api\AdminApiController::class, 'storeContent'])->name('api.admin.content.store');
    Route::put('/content/{type}/{id}', [\App\Http\Controllers\Api\AdminApiController::class, 'updateContent'])->name('api.admin.content.update');
    Route::delete('/content/{type}/{id}', [\App\Http\Controllers\Api\AdminApiController::class, 'deleteContent'])->name('api.admin.content.destroy');
    Route::get('/messages', [\App\Http\Controllers\Api\AdminApiController::class, 'messages'])->name('api.admin.messages.index');
    Route::patch('/messages/{id}/read', [\App\Http\Controllers\Api\AdminApiController::class, 'markMessageRead'])->name('api.admin.messages.read');
    Route::delete('/messages/{id}', [\App\Http\Controllers\Api\AdminApiController::class, 'deleteMessage'])->name('api.admin.messages.destroy');
    Route::get('/visitors', [\App\Http\Controllers\Api\AdminApiController::class, 'visitors'])->name('api.admin.visitors.index');
    Route::get('/users', [\App\Http\Controllers\Api\AdminApiController::class, 'users'])->name('api.admin.users.index');
    Route::get('/roles', [\App\Http\Controllers\Api\AdminApiController::class, 'roles'])->name('api.admin.roles.index');
    Route::get('/profile', [\App\Http\Controllers\Api\AdminApiController::class, 'getProfile'])->name('api.admin.profile.get');
    Route::put('/profile', [\App\Http\Controllers\Api\AdminApiController::class, 'updateProfile'])->name('api.admin.profile.update');
});
