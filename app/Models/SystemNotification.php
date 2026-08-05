<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $table = 'system_notifications';

    protected $fillable = [
        'user_id',
        'note_id',
        'type',
        'title',
        'message',
        'is_read',
        'notified_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'notified_at' => 'datetime',
    ];

    /**
     * Cache publisher names while rendering a notification list so old
     * news notifications do not execute the same lookup repeatedly.
     *
     * @var array<int, string|null>
     */
    private static array $newsPublisherNames = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * New notifications already store the publisher's name in the title.
     * This accessor also fixes previously saved rows whose title was the
     * old hard-coded value: "New News & Updates Post".
     */
    public function getTitleAttribute($value): string
    {
        if (($this->attributes['type'] ?? null) !== 'news_update_published') {
            return (string) $value;
        }

        $storedTitle = trim((string) $value);

        if (
            $storedTitle !== ''
            && str_ends_with(strtolower($storedTitle), ' just posted a new update')
        ) {
            return $storedTitle;
        }

        $publisherName = $this->resolveNewsPublisherName();

        if ($publisherName !== null && $publisherName !== '') {
            return $publisherName . ' just posted a new update';
        }

        return $storedTitle !== ''
            ? $storedTitle
            : 'An ArkCrest account just posted a new update';
    }

    private function resolveNewsPublisherName(): ?string
    {
        $postId = (int) ($this->attributes['note_id'] ?? 0);

        if ($postId <= 0) {
            return null;
        }

        if (array_key_exists($postId, self::$newsPublisherNames)) {
            return self::$newsPublisherNames[$postId];
        }

        $post = NewsPost::query()
            ->with(['updater:id,name', 'creator:id,name'])
            ->find($postId);

        $publisherName = trim((string) (
            $post?->updater?->name
            ?? $post?->creator?->name
            ?? ''
        ));

        self::$newsPublisherNames[$postId] = $publisherName !== ''
            ? $publisherName
            : null;

        return self::$newsPublisherNames[$postId];
    }

    public static function notify(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?int $noteId = null
    ): void {
        static::create([
            'user_id' => $userId,
            'note_id' => $noteId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
            'notified_at' => now(),
        ]);
    }
}
