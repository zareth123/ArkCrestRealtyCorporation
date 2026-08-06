<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Testimonial extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'client_name', 'client_role', 'quote',
        'avatar_disk', 'avatar_path', 'status', 'sort_order',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        // Only remove the avatar file on a permanent purge, never on a
        // normal (soft) delete, so restore() always has the file to
        // bring back.
        static::deleting(function (Testimonial $testimonial): void {
            if ($testimonial->isForceDeleting() && $testimonial->avatar_disk && $testimonial->avatar_path) {
                try {
                    Storage::disk($testimonial->avatar_disk)->delete($testimonial->avatar_path);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function getHasAvatarAttribute(): bool
    {
        return !empty($this->avatar_path);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->has_avatar) {
            return null;
        }

        return route('testimonials.avatar', [
            'testimonial' => $this->getKey(),
            'v' => optional($this->updated_at)->timestamp,
        ]);
    }

    public function getInitialsAttribute(): string
    {
        $words = array_filter(preg_split('/\s+/', trim((string) $this->client_name)) ?: []);

        if (empty($words)) {
            return '?';
        }

        $first = mb_substr((string) reset($words), 0, 1);
        $last = count($words) > 1 ? mb_substr((string) end($words), 0, 1) : '';

        return mb_strtoupper($first . $last);
    }
}