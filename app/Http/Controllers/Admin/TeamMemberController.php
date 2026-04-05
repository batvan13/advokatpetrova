<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Support\GalleryImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamMemberController extends Controller
{
    public function index()
    {
        $members = TeamMember::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.team_members.index', compact('members'));
    }

    public function create()
    {
        return view('admin.team_members.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $data = $this->prepare($validated);

        if ($request->hasFile('image')) {
            try {
                $data['image_path'] = app(GalleryImageProcessor::class)->process(
                    $request->file('image'),
                    'team'
                );
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->withErrors(['image' => 'Грешка при обработка на изображението.']);
            }
        }

        TeamMember::create($data);

        return redirect()->route('admin.team-members.index')
            ->with('success', 'Членът на екипа беше добавен успешно.');
    }

    public function edit(TeamMember $teamMember)
    {
        return view('admin.team_members.edit', ['member' => $teamMember]);
    }

    public function update(Request $request, TeamMember $teamMember)
    {
        $validated = $request->validate($this->rules());

        $data = $this->prepare($validated);

        if ($request->hasFile('image')) {
            try {
                $newPath = app(GalleryImageProcessor::class)->process(
                    $request->file('image'),
                    'team'
                );
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->withErrors(['image' => 'Грешка при обработка на изображението.']);
            }

            $oldPath = $teamMember->image_path;
            $data['image_path'] = $newPath;
        }

        $teamMember->update($data);

        if (isset($newPath) && ! empty($oldPath) && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('admin.team-members.index')
            ->with('success', 'Записът беше обновен успешно.');
    }

    public function destroy(TeamMember $teamMember)
    {
        $name = $teamMember->name;
        $path = $teamMember->image_path;
        $teamMember->delete();

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return redirect()->route('admin.team-members.index')
            ->with('success', '„'.$name.'“ беше изтрит от екипа.');
    }

    public function toggle(TeamMember $teamMember)
    {
        $newStatus = ! $teamMember->is_active;
        $teamMember->update(['is_active' => $newStatus]);

        $message = $newStatus
            ? '„'.$teamMember->name.'“ е активиран(а).'
            : '„'.$teamMember->name.'“ е деактивиран(а).';

        return redirect()
            ->route('admin.team-members.index')
            ->with('success', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255', 'email'],
            'image' => ['nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png,webp'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        return [
            'name' => $validated['name'],
            'position' => $validated['position'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];
    }
}
