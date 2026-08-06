<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Award extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'recipient_name', 'title', 'image_disk', 'image_path', 'status', 'sort_order',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        // Only actually remove the image file once the row is truly gone for
        // good (permanent purge), never on a normal soft delete — a soft
        // delete must leave the file untouched so restore() has something
        // to bring back.
        static::deleting(function (Award $award): void {
            if ($award->isForceDeleting() && $award->image_disk && $award->image_path) {
                try {
                    Storage::disk($award->image_disk)->delete($award->image_path);
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

    public function getHasImageAttribute(): bool
    {
        return !empty($this->image_path);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->has_image) {
            return null;
        }

        return route('awards.image', [
            'award' => $this->getKey(),
            'v' => optional($this->updated_at)->timestamp,
        ]);
    }
}