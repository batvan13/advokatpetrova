<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class ChatMessage extends Model
{
    public const SENDER_CLIENT = 'client';
    public const SENDER_LAWYER = 'lawyer';

    public const SENDER_TYPES = [
        self::SENDER_CLIENT,
        self::SENDER_LAWYER,
    ];

    protected $fillable = [
        'chat_session_id',
        'sender_type',
        'message',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (! self::isValidSenderType($model->sender_type)) {
                throw new InvalidArgumentException(
                    'Unsupported chat message sender_type: ' . (string) $model->sender_type
                );
            }
        });
    }

    public static function isValidSenderType(?string $senderType): bool
    {
        return in_array($senderType, self::SENDER_TYPES, true);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }
}
