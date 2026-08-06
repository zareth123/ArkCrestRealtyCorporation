<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class NewsPost extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // A normal delete() must only trash the post — media rows and their
        // files are left completely untouched so restore() brings the post
        // back with working attachments. Only on a genuine permanent purge
        // do we remove the attachment files: the news_post_media rows
        // themselves are removed automatically via the DB's cascade-on-delete
        // foreign key, but that cascade never touches the filesystem, so we
        // have to clean those up ourselves here before the row disappears.
        static::deleting(function (NewsPost $post): void {
            if (!$post->isForceDeleting()) {
                return;
            }

            foreach ($post->media as $media) {
                try {
                    Storage::disk($media->disk)->delete($media->path);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });
    }

    public function media(): HasMany
    {
        return $this->hasMany(NewsPostMedia::class)
            ->orderBy('sort_order')
            ->orderBy('id');
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
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getPublicationStateAttribute(): string
    {
        return $this->status === 'published' && $this->published_at !== null
            ? 'Published'
            : 'Draft';
    }

    public function getIsPublicAttribute(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->isPast();
    }
}