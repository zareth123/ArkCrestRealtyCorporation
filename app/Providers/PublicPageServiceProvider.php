<?php

namespace App\Providers;

use App\Http\Controllers\Admin\AwardController;
use App\Http\Controllers\Admin\NewsPostController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\LandingController;
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

            Route::middleware(['auth', 'no.cache', 'page.visible'])
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

            Route::get(
                '/testimonials/avatar/{testimonial}',
                [LandingController::class, 'testimonialAvatar']
            )->name('testimonials.avatar');

            Route::middleware(['auth', 'no.cache', 'page.visible'])
                ->prefix('admin/testimonials')
                ->name('admin.testimonials.')
                ->group(function (): void {
                    Route::get('/', [TestimonialController::class, 'index'])
                        ->name('index');
                    Route::post('/', [TestimonialController::class, 'store'])
                        ->name('store');
                    Route::put('/{testimonial}', [TestimonialController::class, 'update'])
                        ->name('update');
                    Route::delete('/{testimonial}', [TestimonialController::class, 'destroy'])
                        ->name('destroy');
                    Route::delete('/{testimonial}/avatar', [TestimonialController::class, 'destroyAvatar'])
                        ->name('avatar.destroy');
                });

            Route::get(
                '/awards/image/{award}',
                [LandingController::class, 'awardImage']
            )->name('awards.image');

            Route::middleware(['auth', 'no.cache', 'page.visible'])
                ->prefix('admin/awards')
                ->name('admin.awards.')
                ->group(function (): void {
                    Route::get('/', [AwardController::class, 'index'])
                        ->name('index');
                    Route::post('/', [AwardController::class, 'store'])
                        ->name('store');
                    Route::put('/{award}', [AwardController::class, 'update'])
                        ->name('update');
                    Route::delete('/{award}', [AwardController::class, 'destroy'])
                        ->name('destroy');
                    Route::delete('/{award}/image', [AwardController::class, 'destroyImage'])
                        ->name('image.destroy');
                });
        });
    }
}