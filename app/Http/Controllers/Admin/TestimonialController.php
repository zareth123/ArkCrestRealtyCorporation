<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class TestimonialController extends Controller
{
    private const MAX_FILE_SIZE_KB = 25600; // 25 MB raw upload ceiling — actual stored file is auto-compressed well below this
    private const MAX_AVATAR_DIMENSION = 1000; // longest side, in px, after compression

    public function index(): View
    {
        $this->guardAdmin();

        $testimonials = Testimonial::query()
            ->with(['creator', 'updater'])
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.testimonials.index', [
            'testimonials' => $testimonials,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->guardAdmin();

        $validated = $this->validateTestimonial($request);
        $storedFile = null;

        try {
            DB::beginTransaction();

            $testimonial = Testimonial::create([
                'client_name' => $validated['client_name'],
                'client_role' => $validated['client_role'] ?? null,
                'quote' => $validated['quote'],
                'status' => $validated['status'],
                'sort_order' => $validated['sort_order'] ?? 0,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            if ($request->hasFile('avatar')) {
                $storedFile = $this->storeAvatar($request, $testimonial);
            }

            DB::commit();

            return redirect()
                ->route('admin.testimonials.index')
                ->with('testimonial_success', 'Feedback created successfully.');
        } catch (Throwable $e) {
            DB::rollBack();
            $this->deleteStoredFile($storedFile);
            report($e);

            return back()
                ->withInput()
                ->with('testimonial_error', 'The feedback could not be created. Please try again.');
        }
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $this->guardAdmin();

        $validated = $this->validateTestimonial($request);
        $storedFile = null;
        $previousFile = null;

        try {
            DB::beginTransaction();

            $testimonial->update([
                'client_name' => $validated['client_name'],
                'client_role' => $validated['client_role'] ?? null,
                'quote' => $validated['quote'],
                'status' => $validated['status'],
                'sort_order' => $validated['sort_order'] ?? 0,
                'updated_by' => auth()->id(),
            ]);

            if ($request->hasFile('avatar')) {
                $previousFile = [
                    'disk' => $testimonial->avatar_disk,
                    'path' => $testimonial->avatar_path,
                ];

                $storedFile = $this->storeAvatar($request, $testimonial);
            }

            DB::commit();

            $this->deleteStoredFile($previousFile);

            return redirect()
                ->route('admin.testimonials.index')
                ->with('testimonial_success', 'Feedback updated successfully.');
        } catch (Throwable $e) {
            DB::rollBack();
            $this->deleteStoredFile($storedFile);
            report($e);

            return back()
                ->withInput()
                ->with('testimonial_error', 'The feedback could not be updated. Please try again.');
        }
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->guardAdmin();

        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete feedback.');
        }

        try {
            // A soft delete only trashes the row — the avatar file is left on
            // disk untouched so it's still there if this gets restored from
            // Deleted Records. It's only actually removed on a permanent purge
            // (see Testimonial::booted()).
            DB::transaction(function () use ($testimonial): void {
                $testimonial->delete();
            });

            return redirect()
                ->route('admin.testimonials.index')
                ->with('testimonial_success', 'Feedback deleted successfully.');
        } catch (Throwable $e) {
            report($e);

            return back()->with('testimonial_error', 'The feedback could not be deleted.');
        }
    }

    public function destroyAvatar(Testimonial $testimonial): RedirectResponse
    {
        $this->guardAdmin();

        try {
            $disk = $testimonial->avatar_disk;
            $path = $testimonial->avatar_path;

            $testimonial->update([
                'avatar_disk' => null,
                'avatar_path' => null,
                'updated_by' => auth()->id(),
            ]);

            if ($disk && $path) {
                Storage::disk($disk)->delete($path);
            }

            return back()->with('testimonial_success', 'Photo removed successfully.');
        } catch (Throwable $e) {
            report($e);

            return back()->with('testimonial_error', 'The photo could not be removed.');
        }
    }

    private function validateTestimonial(Request $request): array
    {
        return $request->validate([
            'client_name' => ['required', 'string', 'max:120'],
            'client_role' => ['nullable', 'string', 'max:120'],
            'quote' => ['required', 'string', 'max:2000'],
            'status' => ['required', 'in:draft,published'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:' . self::MAX_FILE_SIZE_KB],
        ], [
            'avatar.mimes' => 'Photo must be a JPG, PNG, GIF, or WEBP file.',
            'avatar.max' => 'Photo must not exceed 25 MB.',
        ]);
    }

    private function storeAvatar(Request $request, Testimonial $testimonial): array
    {
        $file = $request->file('avatar');
        $disk = $this->avatarDisk();
        $directory = 'testimonials/' . $testimonial->id;

        $compressed = $this->compressImage(file_get_contents($file->getRealPath()));

        if ($compressed) {
            $filename = Str::uuid()->toString() . '.jpg';
            $stored = Storage::disk($disk)->put(
                $directory . '/' . $filename,
                $compressed,
                ['visibility' => 'public']
            );
            $path = $stored ? $directory . '/' . $filename : null;
        } else {
            // GD unavailable or couldn't decode the file — fall back to storing the original untouched.
            $extension = strtolower(
                $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg'
            );
            $filename = Str::uuid()->toString() . '.' . preg_replace('/[^a-z0-9]/i', '', $extension);
            $path = Storage::disk($disk)->putFileAs($directory, $file, $filename, ['visibility' => 'public']);
        }

        if (!$path) {
            throw new \RuntimeException('Unable to store the uploaded photo.');
        }

        $testimonial->update(['avatar_disk' => $disk, 'avatar_path' => $path]);

        return ['disk' => $disk, 'path' => $path];
    }

    /**
     * Downscales an image to MAX_AVATAR_DIMENSION on its longest side and
     * re-encodes it as JPEG, so uploads of any original resolution/format
     * end up as one small, predictable file size on disk. Returns null if
     * GD isn't available or the file can't be decoded, so the caller can
     * fall back to storing the original.
     */
    private function compressImage(string $raw): ?string
    {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }

        $image = @imagecreatefromstring($raw);
        if (!$image) {
            return null;
        }

        $width  = imagesx($image);
        $height = imagesy($image);
        $longestSide = max($width, $height);

        if ($longestSide > self::MAX_AVATAR_DIMENSION) {
            $scale     = self::MAX_AVATAR_DIMENSION / $longestSide;
            $newWidth  = max(1, (int) round($width * $scale));
            $newHeight = max(1, (int) round($height * $scale));

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            // Flatten onto white for images with transparency (PNG/GIF), since JPEG has no alpha channel.
            $white = imagecolorallocate($resized, 255, 255, 255);
            imagefill($resized, 0, 0, $white);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        ob_start();
        $ok = imagejpeg($image, null, 82);
        $jpegData = ob_get_clean();
        imagedestroy($image);

        return ($ok && !empty($jpegData)) ? $jpegData : null;
    }

    private function deleteStoredFile(?array $file): void
    {
        if (!$file || empty($file['disk']) || empty($file['path'])) {
            return;
        }

        try {
            Storage::disk($file['disk'])->delete($file['path']);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function avatarDisk(): string
    {
        return config('filesystems.news_media_disk', config('filesystems.avatar_disk', 'public'));
    }

    private function guardAdmin(): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Only signed-in staff can manage feedback.');
        }

        if ($user->isAdmin()) {
            return;
        }

        if (in_array('admin.testimonials', $user->hidden_pages ?? [])) {
            abort(403, 'You do not have access to Feedback Management.');
        }
    }
}