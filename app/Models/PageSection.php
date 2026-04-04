<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'page',
        'section',
        'title',
        'subtitle',
        'content',
        'meta',
        'faq',
    ];

    protected $casts = [
        'meta' => 'array',
        'faq' => 'array',
    ];

    public function getButtonTextAttribute(): ?string
    {
        return $this->meta['button_text'] ?? null;
    }

    public function getButtonUrlAttribute(): ?string
    {
        return $this->meta['button_url'] ?? null;
    }

    public function getImagePathAttribute(): ?string
    {
        return $this->meta['image_path'] ?? null;
    }

    public function getPillsAttribute(): array
    {
        $raw = $this->meta['pills'] ?? [];

        if (! is_array($raw)) {
            return [];
        }

        $pills = [];

        foreach ($raw as $item) {
            if (! is_array($item)) {
                continue;
            }

            $text = trim((string) ($item['text'] ?? ''));

            if ($text === '') {
                continue;
            }

            $pills[] = [
                'text' => $text,
                'url'  => trim((string) ($item['url'] ?? '')),
            ];
        }

        return $pills;
    }
}
