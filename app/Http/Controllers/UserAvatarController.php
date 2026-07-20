<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserAvatarController extends Controller
{
    public function show(User $user): StreamedResponse
    {
        abort_unless($user->avatar, 404);

        $candidateDisks = array_unique([
            $user->avatarStorageDisk(),
            'public',
        ]);

        foreach ($candidateDisks as $disk) {
            try {
                if (!Storage::disk($disk)->exists($user->avatar)) {
                    continue;
                }

                return Storage::disk($disk)->response(
                    $user->avatar,
                    null,
                    [
                        'Cache-Control' => 'private, max-age=3600',
                    ]
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        abort(404);
    }
}
