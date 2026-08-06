<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LandingController extends Controller
{
    public function index(): View
    {
        $testimonials = Testimonial::query()
            ->publiclyVisible()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        $awards = Award::query()
            ->publiclyVisible()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return view('landing', [
            'testimonials' => $testimonials,
            'awards' => $awards,
        ]);
    }

    public function testimonialAvatar(Testimonial $testimonial): Response
    {
        abort_unless($testimonial->avatar_disk && $testimonial->avatar_path, 404);

        $disk = Storage::disk($testimonial->avatar_disk);

        try {
            abort_unless($disk->exists($testimonial->avatar_path), 404);

            if ($testimonial->avatar_disk === 's3') {
                try {
                    $temporaryUrl = $disk->temporaryUrl($testimonial->avatar_path, now()->addMinutes(30));
                    return redirect()->away($temporaryUrl);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }

            return $disk->response($testimonial->avatar_path, null, [
                'Cache-Control' => 'public, max-age=3600',
            ]);
        } catch (Throwable $exception) {
            report($exception);
            abort(404);
        }
    }

    public function awardImage(Award $award): Response
    {
        abort_unless($award->image_disk && $award->image_path, 404);

        $disk = Storage::disk($award->image_disk);

        try {
            abort_unless($disk->exists($award->image_path), 404);

            if ($award->image_disk === 's3') {
                try {
                    $temporaryUrl = $disk->temporaryUrl($award->image_path, now()->addMinutes(30));
                    return redirect()->away($temporaryUrl);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }

            return $disk->response($award->image_path, null, [
                'Cache-Control' => 'public, max-age=3600',
            ]);
        } catch (Throwable $exception) {
            report($exception);
            abort(404);
        }
    }
}