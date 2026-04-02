<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public const KEYS = [
        'site_name',
        'site_tagline',
        'contact_email',
        'contact_phone',
        'address',
        'google_maps_url',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
    ];

    public static function get(string $key, $default = null)
    {
        return static::query()
            ->where('key', $key)
            ->value('value') ?? $default;
    }
}
