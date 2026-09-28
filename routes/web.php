<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NewsClassController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\FrontController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 前台路由
|--------------------------------------------------------------------------
*/
Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/category/{newsclass}', [FrontController::class, 'category'])->name('front.category');
Route::get('/article/{newsContent}', [FrontController::class, 'show'])->name('front.show');
Route::get('/search', [FrontController::class, 'search'])->name('front.search');
Route::get('/archive', [FrontController::class, 'archive'])->name('front.archive');
Route::get('/tag/{tag}', [FrontController::class, 'tag'])->name('front.tag');
Route::get('/feed', [FrontController::class, 'feed'])->name('front.feed');
Route::get('/sitemap.xml', [FrontController::class, 'sitemap'])->name('front.sitemap');

/*
|--------------------------------------------------------------------------
| 后台路由
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login.attempt');
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('password', [AuthController::class, 'showPasswordForm'])->name('password.edit');
        Route::put('password', [AuthController::class, 'updatePassword'])->name('password.update');

        Route::post('upload', [UploadController::class, 'store'])->name('upload');

        Route::resource('news-class', NewsClassController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('tags', TagController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('news', NewsController::class);

        Route::get('config', [ConfigController::class, 'edit'])->name('config.edit');
        Route::put('config', [ConfigController::class, 'update'])->name('config.update');
    });
});
