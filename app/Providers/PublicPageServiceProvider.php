<?php

namespace App\Providers;

use App\Http\Controllers\Admin\NewsPostController;
use App\Http\Controllers\NewsUpdateController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PublicPageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware('web')->group(function (): void {
            Route::get('/news-updates', [NewsUpdateController::class, 'index'])
                ->name('news-updates');

            Route::get(
                '/news-updates/media/{media}',
                [NewsUpdateController::class, 'media']
            )->name('news-updates.media');

            Route::middleware(['auth', 'no.cache', 'admin'])
                ->prefix('admin/news-updates')
                ->name('admin.news-updates.')
                ->group(function (): void {
                    Route::get('/', [NewsPostController::class, 'index'])
                        ->name('index');
                    Route::post('/', [NewsPostController::class, 'store'])
                        ->name('store');
                    Route::put('/{newsPost}', [NewsPostController::class, 'update'])
                        ->name('update');
                    Route::delete('/{newsPost}', [NewsPostController::class, 'destroy'])
                        ->name('destroy');
                    Route::delete('/media/{media}', [NewsPostController::class, 'destroyMedia'])
                        ->name('media.destroy');
                });
        });
    }
}
