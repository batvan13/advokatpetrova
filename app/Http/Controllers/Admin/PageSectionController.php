<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use App\Support\GalleryImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PageSectionController extends Controller
{
    public function index()
    {
        $sections = PageSection::query()
            ->orderBy('page')
            ->orderBy('section')
            ->get()
            ->reject(fn (PageSection $section) => ($section->page === 'home' && $section->section === 'faq')
                || ($section->page === 'home' && $section->section === 'gallery_preview')
                || $section->page === 'gallery')
            ->values();

        return view('admin.sections.index', compact('sections'));
    }

    public function edit(PageSection $pageSection)
    {
        return view('admin.sections.edit', compact('pageSection'));
    }

    public function update(Request $request, PageSection $pageSection)
    {
        if ($this->isHomeFaqSection($pageSection)) {
            $validated = $request->validate([
                'title'            => ['nullable', 'string', 'max:255'],
                'faq'              => ['nullable', 'array'],
                'faq.*.question'   => ['nullable', 'string', 'max:2000'],
                'faq.*.answer'     => ['nullable', 'string', 'max:20000'],
            ]);

            $titleRaw = $validated['title'] ?? null;
            $title = ($titleRaw === null || trim((string) $titleRaw) === '')
                ? null
                : trim((string) $titleRaw);

            $pageSection->update([
                'title' => $title,
                'faq' => $this->normalizeFaqForPersistence($request->input('faq')),
            ]);
        } elseif ($this->isHomeHeroSection($pageSection)) {
            $validated = $request->validate([
                'title'              => ['nullable', 'string', 'max:255'],
                'subtitle'           => ['nullable', 'string', 'max:255'],
                'content'            => ['nullable', 'string'],
                'meta.button_text'   => ['nullable', 'string', 'max:255'],
                'meta.button_url'    => ['nullable', 'string', 'max:255'],
                'meta.pills'         => ['nullable', 'array'],
                'meta.pills.*.text'  => ['nullable', 'string', 'max:255'],
                'meta.pills.*.url'   => ['nullable', 'string', 'max:255'],
                'image'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'remove_image'       => ['nullable', 'boolean'],
            ]);

            // Start from existing meta to preserve any previously stored keys.
            $meta = is_array($pageSection->meta) ? $pageSection->meta : [];

            $meta['button_text'] = $validated['meta']['button_text'] ?? null;
            $meta['button_url']  = $validated['meta']['button_url']  ?? null;

            // Normalize pills: discard empty rows, keep only rows with non-empty text.
            $rawPills = $validated['meta']['pills'] ?? [];
            $pills    = [];
            foreach ((array) $rawPills as $pill) {
                if (! is_array($pill)) {
                    continue;
                }
                $text = trim((string) ($pill['text'] ?? ''));
                if ($text === '') {
                    continue;
                }
                $pills[] = [
                    'text' => $text,
                    'url'  => trim((string) ($pill['url'] ?? '')),
                ];
            }
            $meta['pills'] = $pills ?: null;

            $removeImage = $request->boolean('remove_image', false);

            if ($request->hasFile('image')) {
                $oldPath = $meta['image_path'] ?? null;
                $newPath = null;

                try {
                    $newPath = app(GalleryImageProcessor::class)->process($request->file('image'), 'hero');
                } catch (\Throwable $e) {
                    return back()
                        ->withInput()
                        ->withErrors(['image' => 'Грешка при обработка на изображението.']);
                }

                $meta['image_path'] = $newPath;

                try {
                    $pageSection->update([
                        'title'    => $validated['title']    ?? null,
                        'subtitle' => $validated['subtitle'] ?? null,
                        'content'  => $validated['content']  ?? null,
                        'meta'     => $meta,
                    ]);
                } catch (\Throwable $e) {
                    if ($newPath && Storage::disk('public')->exists($newPath)) {
                        Storage::disk('public')->delete($newPath);
                    }

                    return back()
                        ->withInput()
                        ->withErrors(['image' => 'Грешка при запис на изображението.']);
                }

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            } elseif ($removeImage) {
                $oldPath              = $meta['image_path'] ?? null;
                $meta['image_path']   = null;

                $pageSection->update([
                    'title'    => $validated['title']    ?? null,
                    'subtitle' => $validated['subtitle'] ?? null,
                    'content'  => $validated['content']  ?? null,
                    'meta'     => $meta,
                ]);

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            } else {
                // No image change — $meta['image_path'] is already preserved from $pageSection->meta.
                $pageSection->update([
                    'title'    => $validated['title']    ?? null,
                    'subtitle' => $validated['subtitle'] ?? null,
                    'content'  => $validated['content']  ?? null,
                    'meta'     => $meta,
                ]);
            }
        } elseif ($this->isHomeAboutPreviewSection($pageSection)) {
            $validated = $request->validate([
                'title'            => ['nullable', 'string', 'max:255'],
                'subtitle'         => ['nullable', 'string', 'max:255'],
                'content'          => ['nullable', 'string'],
                'meta.button_text' => ['nullable', 'string', 'max:255'],
                'meta.button_url'  => ['nullable', 'string', 'max:255'],
                'image'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'remove_image'     => ['nullable', 'boolean'],
            ]);

            // Start from existing meta to preserve any previously stored keys.
            $meta = is_array($pageSection->meta) ? $pageSection->meta : [];

            $meta['button_text'] = $validated['meta']['button_text'] ?? null;
            $meta['button_url']  = $validated['meta']['button_url']  ?? null;

            $removeImage = $request->boolean('remove_image', false);

            if ($request->hasFile('image')) {
                $oldPath = $meta['image_path'] ?? null;
                $newPath = null;

                try {
                    $newPath = app(GalleryImageProcessor::class)->process($request->file('image'), 'about');
                } catch (\Throwable $e) {
                    return back()
                        ->withInput()
                        ->withErrors(['image' => 'Грешка при обработка на изображението.']);
                }

                $meta['image_path'] = $newPath;

                try {
                    $pageSection->update([
                        'title'    => $validated['title']    ?? null,
                        'subtitle' => $validated['subtitle'] ?? null,
                        'content'  => $validated['content']  ?? null,
                        'meta'     => $meta,
                    ]);
                } catch (\Throwable $e) {
                    if ($newPath && Storage::disk('public')->exists($newPath)) {
                        Storage::disk('public')->delete($newPath);
                    }

                    return back()
                        ->withInput()
                        ->withErrors(['image' => 'Грешка при запис на изображението.']);
                }

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            } elseif ($removeImage) {
                $oldPath              = $meta['image_path'] ?? null;
                $meta['image_path']   = null;

                $pageSection->update([
                    'title'    => $validated['title']    ?? null,
                    'subtitle' => $validated['subtitle'] ?? null,
                    'content'  => $validated['content']  ?? null,
                    'meta'     => $meta,
                ]);

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            } else {
                // No image change — $meta['image_path'] is already preserved from $pageSection->meta.
                $pageSection->update([
                    'title'    => $validated['title']    ?? null,
                    'subtitle' => $validated['subtitle'] ?? null,
                    'content'  => $validated['content']  ?? null,
                    'meta'     => $meta,
                ]);
            }
        } else {
            $validated = $request->validate([
                'title'            => ['nullable', 'string', 'max:255'],
                'subtitle'         => ['nullable', 'string', 'max:255'],
                'content'          => ['nullable', 'string'],
                'meta.button_text' => ['nullable', 'string', 'max:255'],
                'meta.button_url'  => ['nullable', 'string', 'max:255'],
            ]);

            $pageSection->update([
                'title'    => $validated['title']    ?? null,
                'subtitle' => $validated['subtitle'] ?? null,
                'content'  => $validated['content']  ?? null,
                'meta'     => [
                    'button_text' => $validated['meta']['button_text'] ?? null,
                    'button_url'  => $validated['meta']['button_url']  ?? null,
                ],
            ]);
        }

        return redirect()
            ->route('admin.sections.edit', $pageSection)
            ->with('success', 'Секцията беше обновена успешно.');
    }

    private function isHomeFaqSection(PageSection $pageSection): bool
    {
        return $pageSection->page === 'home' && $pageSection->section === 'faq';
    }

    private function isHomeHeroSection(PageSection $pageSection): bool
    {
        return $pageSection->page === 'home' && $pageSection->section === 'hero';
    }

    private function isHomeAboutPreviewSection(PageSection $pageSection): bool
    {
        return $pageSection->page === 'home' && $pageSection->section === 'about_preview';
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
}
