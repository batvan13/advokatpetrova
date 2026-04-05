<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\GalleryImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->serviceValidationRules());

        $validated['faq'] = $this->normalizeFaqForPersistence($request->input('faq'));

        unset($validated['image'], $validated['remove_image']);

        $data = $this->prepare($validated);

        if ($request->hasFile('image')) {
            try {
                $data['image'] = app(GalleryImageProcessor::class)->process(
                    $request->file('image'),
                    'services'
                );
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->withErrors(['image' => 'Грешка при обработка на изображението.']);
            }
        }

        Service::create($data);

        return redirect()->route('admin.services.index')
            ->with('success', 'Услугата беше добавена успешно.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate($this->serviceValidationRules($service));

        if ($request->has('faq')) {
            $validated['faq'] = $this->normalizeFaqForPersistence($request->input('faq'));
        }

        $removeImage = (bool) ($validated['remove_image'] ?? false);
        unset($validated['image'], $validated['remove_image']);

        $data = $this->prepare($validated, $service);

        $oldImagePath = $service->image;

        if ($request->hasFile('image')) {
            try {
                $newPath = app(GalleryImageProcessor::class)->process(
                    $request->file('image'),
                    'services'
                );
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->withErrors(['image' => 'Грешка при обработка на изображението.']);
            }

            $data['image'] = $newPath;
            $service->update($data);

            if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
        } elseif ($removeImage && $oldImagePath) {
            $data['image'] = null;
            $service->update($data);

            if (Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
        } else {
            $service->update($data);
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Услугата беше обновена успешно.');
    }

    private function prepare(array $data, ?Service $existing = null): array
    {
        // Slug: use submitted value or auto-generate from title
        $baseSlug = Str::slug($data['slug'] ?? $data['title']);

        // Ensure uniqueness by appending a numeric suffix when needed
        $slug      = $baseSlug;
        $suffix    = 1;
        $ignoreId  = $existing?->id;

        while (
            Service::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        $data['slug']       = $slug;
        $data['is_active']  = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int)  ($data['sort_order'] ?? 0);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceValidationRules(?Service $forUpdate = null): array
    {
        $slugRule = ['nullable', 'string', 'max:255', 'unique:services,slug'];
        if ($forUpdate !== null) {
            $slugRule = ['nullable', 'string', 'max:255', Rule::unique('services', 'slug')->ignore($forUpdate->id)];
        }

        return [
            'title'               => ['required', 'string', 'max:255'],
            'slug'                => $slugRule,
            'short_description'   => ['nullable', 'string'],
            'full_description'    => ['nullable', 'string'],
            'icon'                => ['nullable', 'string', 'max:255'],
            'image'               => ['nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png,webp'],
            'remove_image'        => ['nullable', 'boolean'],
            'is_active'           => ['boolean'],
            'sort_order'          => ['integer', 'min:0'],
            'faq'                 => ['nullable', 'array'],
            'faq.*.question'      => ['nullable', 'string', 'max:2000'],
            'faq.*.answer'        => ['nullable', 'string', 'max:20000'],
        ];
    }

    /**
     * @return list<array{question: string, answer: string}>|null
     */
    private function normalizeFaqForPersistence(mixed $faq): ?array
    {
        if (! is_array($faq)) {
            return null;
        }

        $items = [];
        foreach ($faq as $row) {
            if (! is_array($row)) {
                continue;
            }
            $question = trim((string) ($row['question'] ?? ''));
            $answer = trim((string) ($row['answer'] ?? ''));

            if ($question === '' && $answer === '') {
                continue;
            }

            if ($question === '' || $answer === '') {
                throw ValidationException::withMessages([
                    'faq' => [
                        'Всеки ред с попълнено поле трябва да има и въпрос, и отговор. Изтрий текста от едното поле или допълни другото.',
                    ],
                ]);
            }

            $items[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $items === [] ? null : $items;
    }

    public function destroy(Service $service)
    {
        $title = $service->title;
        $imagePath = $service->image;
        $service->delete();

        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Услугата "' . $title . '" беше изтрита.');
    }

    public function toggle(Service $service)
    {
        $newStatus = ! $service->is_active;

        $service->update(['is_active' => $newStatus]);

        $message = $newStatus
            ? 'Услугата "' . $service->title . '" е активирана.'
            : 'Услугата "' . $service->title . '" е деактивирана.';

        return redirect()
            ->route('admin.services.index')
            ->with('success', $message);
    }

}
