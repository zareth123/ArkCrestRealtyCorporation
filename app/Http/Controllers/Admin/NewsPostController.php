<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use App\Models\NewsPostMedia;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class NewsPostController extends Controller
{
    private const MAX_FILES_PER_REQUEST = 10;
    private const MAX_FILE_SIZE_KB = 102400; // 100 MB per file

    public function index(): View
    {
        $this->guardAdmin();

        $posts = NewsPost::query()
            ->with(['media', 'creator', 'updater'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.news-posts.index', [
            'posts' => $posts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->guardAdmin();

        $validated = $this->validatePost($request);
        $storedFiles = [];

        try {
            DB::beginTransaction();

            $post = NewsPost::create([
                'title' => $validated['title'],
                'slug' => $this->uniqueSlug($validated['title']),
                'description' => $validated['description'],
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published' ? now() : null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $storedFiles = $this->storeMedia($request, $post);

            DB::commit();

            if ($post->status === 'published') {
                $this->notifyAllUsersAboutPublishedPost($post);
            }

            return redirect()
                ->route('admin.news-updates.index')
                ->with('news_success', 'News post created successfully.');
        } catch (Throwable $e) {
            DB::rollBack();
            $this->deleteStoredFiles($storedFiles);
            report($e);

            return back()
                ->withInput()
                ->with('news_error', 'The news post could not be created. Please try again.');
        }
    }

    public function update(Request $request, NewsPost $newsPost): RedirectResponse
    {
        $this->guardAdmin();

        $validated = $this->validatePost($request);
        $storedFiles = [];
        $wasPublished = $newsPost->status === 'published'
            && $newsPost->published_at !== null;
        $willBePublished = $validated['status'] === 'published';

        try {
            DB::beginTransaction();

            $newsPost->update([
                'title' => $validated['title'],
                'slug' => $this->uniqueSlug($validated['title'], $newsPost->id),
                'description' => $validated['description'],
                'status' => $validated['status'],
                'published_at' => $this->resolveAutomaticPublishedAt(
                    $wasPublished,
                    $willBePublished,
                    $newsPost
                ),
                'updated_by' => auth()->id(),
            ]);

            $storedFiles = $this->storeMedia($request, $newsPost);

            DB::commit();

            if (!$wasPublished && $willBePublished) {
                $this->notifyAllUsersAboutPublishedPost($newsPost->fresh());
            }

            return redirect()
                ->route('admin.news-updates.index')
                ->with('news_success', 'News post updated successfully.');
        } catch (Throwable $e) {
            DB::rollBack();
            $this->deleteStoredFiles($storedFiles);
            report($e);

            return back()
                ->withInput()
                ->with('news_error', 'The news post could not be updated. Please try again.');
        }
    }

    public function destroy(NewsPost $newsPost): RedirectResponse
    {
        $this->guardAdmin();

        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete News & Updates posts.');
        }

        try {
            // A soft delete only trashes the row — attached media rows and
            // their files are left completely untouched so they're still
            // there if this gets restored from Deleted Records. They're only
            // actually removed on a permanent purge (see NewsPost::booted()).
            DB::transaction(function () use ($newsPost): void {
                $newsPost->delete();
            });

            return redirect()
                ->route('admin.news-updates.index')
                ->with('news_success', 'News post deleted successfully.');
        } catch (Throwable $e) {
            report($e);

            return back()->with('news_error', 'The news post could not be deleted.');
        }
    }

    public function destroyMedia(NewsPostMedia $media): RedirectResponse
    {
        $this->guardAdmin();

        try {
            $disk = $media->disk;
            $path = $media->path;

            $media->delete();
            Storage::disk($disk)->delete($path);

            return back()->with('news_success', 'Attachment removed successfully.');
        } catch (Throwable $e) {
            report($e);

            return back()->with('news_error', 'The attachment could not be removed.');
        }
    }

    private function validatePost(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:30000'],
            'status' => ['required', 'in:draft,published'],
            'media' => ['nullable', 'array', 'max:' . self::MAX_FILES_PER_REQUEST],
            'media.*' => [
                'file',
                'mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm',
                'max:' . self::MAX_FILE_SIZE_KB,
            ],
        ], [
            'media.max' => 'You may upload up to ' . self::MAX_FILES_PER_REQUEST . ' files at a time.',
            'media.*.mimes' => 'Attachments must be JPG, PNG, GIF, WEBP, MP4, MOV, or WEBM files.',
            'media.*.max' => 'Each attachment must not exceed 100 MB.',
        ]);
    }

    private function resolveAutomaticPublishedAt(
        bool $wasPublished,
        bool $willBePublished,
        NewsPost $post
    ) {
        if (!$willBePublished) {
            return null;
        }

        if ($wasPublished && $post->published_at !== null) {
            return $post->published_at;
        }

        return now();
    }

    private function notifyAllUsersAboutPublishedPost(NewsPost $post): void
    {
        try {
            // Use the authenticated account that actually performed the publish action.
            // This is more accurate than relying on the post's creator/updater relationship.
            $publisherName = trim((string) (auth()->user()?->name ?? ''));

            if ($publisherName === '') {
                $publisherName = 'An ArkCrest account';
            }

            $notificationTitle = $publisherName . ' just posted a new update';

            User::query()
                ->select('id')
                ->orderBy('id')
                ->chunkById(200, function ($users) use ($post, $notificationTitle): void {
                    foreach ($users as $user) {
                        SystemNotification::notify(
                            $user->id,
                            'news_update_published',
                            $notificationTitle,
                            $post->title,
                            $post->id
                        );
                    }
                });
        } catch (Throwable $e) {
            // Keep the successful post published even if notification creation fails.
            report($e);
        }
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'news-update';
        $slug = $base;
        $suffix = 2;

        while (
            NewsPost::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return array<int, array{disk:string,path:string}>
     */
    private function storeMedia(Request $request, NewsPost $post): array
    {
        $files = $request->file('media', []);
        $stored = [];

        if (!$files) {
            return $stored;
        }

        $disk = $this->mediaDisk();
        $nextSortOrder = ((int) $post->media()->max('sort_order')) + 1;

        foreach ($files as $file) {
            $mimeType = (string) ($file->getMimeType() ?: 'application/octet-stream');
            $mediaType = Str::startsWith($mimeType, 'image/') ? 'image' : 'video';
            $extension = strtolower(
                $file->guessExtension()
                ?: $file->getClientOriginalExtension()
                ?: ($mediaType === 'image' ? 'jpg' : 'mp4')
            );

            $filename = Str::uuid()->toString() . '.' . preg_replace('/[^a-z0-9]/i', '', $extension);
            $directory = 'news-updates/' . $post->id;

            $path = Storage::disk($disk)->putFileAs(
                $directory,
                $file,
                $filename,
                ['visibility' => 'public']
            );

            if (!$path) {
                throw new \RuntimeException('Unable to store one of the uploaded attachments.');
            }

            $stored[] = [
                'disk' => $disk,
                'path' => $path,
            ];

            $post->media()->create([
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'media_type' => $mediaType,
                'size' => (int) $file->getSize(),
                'sort_order' => $nextSortOrder++,
            ]);
        }

        return $stored;
    }

    /**
     * @param array<int, array{disk:string,path:string}> $files
     */
    private function deleteStoredFiles(array $files): void
    {
        foreach ($files as $file) {
            try {
                Storage::disk($file['disk'])->delete($file['path']);
            } catch (Throwable $e) {
                report($e);
            }
        }
    }

    private function mediaDisk(): string
    {
        return config(
            'filesystems.news_media_disk',
            config('filesystems.avatar_disk', 'public')
        );
    }

    private function guardAdmin(): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Only signed-in staff can manage News & Updates posts.');
        }

        if ($user->isAdmin()) {
            return;
        }

        // Defense in depth: the 'page.visible' route middleware already
        // blocks this before it reaches the controller, but we check again
        // here in case this method is ever called from elsewhere.
        if (in_array('admin.news-updates', $user->hidden_pages ?? [])) {
            abort(403, 'You do not have access to News & Updates Posting.');
        }
    }
}