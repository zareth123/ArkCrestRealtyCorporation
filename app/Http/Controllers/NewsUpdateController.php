<?php

namespace App\Http\Controllers;

use App\Models\NewsPost;
use App\Models\NewsPostMedia;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class NewsUpdateController extends Controller
{
    public function index(): View
    {
        $posts = NewsPost::query()
            ->publiclyVisible()
            ->with('media')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9);

        return view('news-updates', [
            'posts' => $posts,
        ]);
    }

    public function media(NewsPostMedia $media): Response
    {
        $disk = Storage::disk($media->disk);

        try {
            abort_unless($disk->exists($media->path), 404);

            if ($media->disk === 's3') {
                try {
                    $temporaryUrl = $disk->temporaryUrl(
                        $media->path,
                        now()->addMinutes(30),
                        [
                            'ResponseContentType' => $media->mime_type,
                            'ResponseContentDisposition' => $this->inlineDisposition(
                                $media->original_name
                            ),
                        ]
                    );

                    return redirect()->away($temporaryUrl);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }

            return $disk->response(
                $media->path,
                $media->original_name,
                [
                    'Content-Type' => $media->mime_type,
                    'Content-Disposition' => $this->inlineDisposition(
                        $media->original_name
                    ),
                    'Cache-Control' => 'public, max-age=3600',
                ]
            );
        } catch (Throwable $exception) {
            report($exception);
            abort(404);
        }
    }

    private function inlineDisposition(string $filename): string
    {
        $safeFilename = str_replace(
            ['"', "\r", "\n"],
            '',
            $filename
        );

        return 'inline; filename="' . $safeFilename . '"';
    }
}
