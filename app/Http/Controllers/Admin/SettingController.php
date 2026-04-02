<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = SiteSetting::whereIn('key', SiteSetting::KEYS)
            ->pluck('value', 'key');

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name'     => ['nullable', 'string', 'max:255'],
            'site_tagline'  => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'address'         => ['nullable', 'string'],
            'google_maps_url' => ['nullable', 'url', 'max:255'],
            'facebook_url'    => ['nullable', 'url', 'max:255'],
            'instagram_url'   => ['nullable', 'url', 'max:255'],
            'linkedin_url'    => ['nullable', 'url', 'max:255'],
        ]);

        foreach ($validated as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key'   => $key],
                ['value' => $value]
            );
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Настройките бяха запазени успешно.');
    }
}
