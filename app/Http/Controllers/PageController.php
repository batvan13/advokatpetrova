<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function home()
    {
        $homeFaqSection = page_section('home', 'faq');

        return view('pages.home', [
            'homeServices' => Service::active()->ordered()->limit(3)->get(),
            'homeFaqSection' => $homeFaqSection,
            'homeFaqItems' => $this->filteredHomeFaqItems($homeFaqSection?->faq),
        ]);
    }

    /**
     * Defensive FAQ list for homepage (canonical shape enforced in admin).
     *
     * @return list<array{question: string, answer: string}>
     */
    private function filteredHomeFaqItems(mixed $faq): array
    {
        if (! is_array($faq)) {
            return [];
        }

        $items = [];
        foreach ($faq as $row) {
            if (! is_array($row)) {
                continue;
            }
            $question = trim((string) ($row['question'] ?? ''));
            $answer = trim((string) ($row['answer'] ?? ''));
            if ($question === '' || $answer === '') {
                continue;
            }
            $items[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $items;
    }

    public function about()
    {
        return view('pages.about', [
            'hero' => page_section('about', 'hero'),
            'content' => page_section('about', 'content'),
        ]);
    }

    public function consultation()
    {
        return view('pages.consultation', [
            'hero' => page_section('consultation', 'hero'),
            'content' => page_section('consultation', 'content'),
        ]);
    }

    public function consultationSuccess()
    {
        return view('pages.consultation-success');
    }

    public function services()
    {
        return view('pages.services', [
            'page' => page_section('services', 'hero'),
            'services' => Service::active()->ordered()->get(),
        ]);
    }

    public function serviceShow(string $slug)
    {
        $service = Service::active()->where('slug', $slug)->firstOrFail();

        $rawDesc = $service->short_description ?: $service->full_description ?: '';
        $seoDescription = Str::limit(
            trim(preg_replace('/\s+/', ' ', strip_tags($rawDesc))),
            160,
            ''
        );

        if ($seoDescription === '') {
            $seoDescription = Str::limit(
                trim(strip_tags($service->title)),
                160,
                ''
            );
        }

        return view('pages.service-show', [
            'service' => $service,
            'seoDescription' => $seoDescription,
            'faqItems' => $this->normalizedServiceFaqItems($service->faq),
        ]);
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    private function normalizedServiceFaqItems(mixed $faq): array
    {
        if (! is_array($faq)) {
            return [];
        }

        $items = [];
        foreach ($faq as $row) {
            if (! is_array($row)) {
                continue;
            }
            $question = isset($row['question']) ? trim((string) $row['question']) : '';
            $answer = isset($row['answer']) ? trim((string) $row['answer']) : '';
            if ($question === '' || $answer === '') {
                continue;
            }
            $items[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $items;
    }

    public function gallery(Request $request)
    {
        $filter = $request->query('type', 'all');
        $allowed = ['all', GalleryItem::TYPE_IMAGE, GalleryItem::TYPE_VIDEO];
        if (! in_array($filter, $allowed, true)) {
            $filter = 'all';
        }

        $query = GalleryItem::active()->ordered();
        if ($filter === GalleryItem::TYPE_IMAGE) {
            $query->where('type', GalleryItem::TYPE_IMAGE);
        } elseif ($filter === GalleryItem::TYPE_VIDEO) {
            $query->where('type', GalleryItem::TYPE_VIDEO);
        }

        $items = $query->get();
        $hasAnyActiveGalleryItems = GalleryItem::active()->exists();

        return view('pages.gallery', [
            'hero' => page_section('gallery', 'hero'),
            'items' => $items,
            'galleryFilter' => $filter,
            'hasAnyActiveGalleryItems' => $hasAnyActiveGalleryItems,
        ]);
    }

    public function contacts()
    {
        $email = setting('contact_email');
        $phone = setting('contact_phone');
        $address = setting('address');
        $facebook = setting('facebook_url');
        $instagram = setting('instagram_url');
        $youtube = setting('youtube_url');

        return view('pages.contacts', [
            'hero' => page_section('contacts', 'hero'),
            'content' => page_section('contacts', 'content'),
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'tel' => $phone ? ('tel:'.preg_replace('/[^\d+]/', '', $phone)) : null,
            'facebook' => $facebook,
            'instagram' => $instagram,
            'youtube' => $youtube,
            'hasContact' => (bool) ($email || $phone || $address),
            'hasSocial' => (bool) ($facebook || $instagram || $youtube),
        ]);
    }

    public function practice()
    {
        return view('pages.practice', [
            'contentSection' => page_section('practice', 'content'),
        ]);
    }

    public function terms()
    {
        return view('pages.terms', [
            'contentSection' => page_section('terms', 'content'),
        ]);
    }

    public function cookiePolicy()
    {
        return view('pages.cookie-policy', [
            'contentSection' => page_section('cookie_policy', 'content'),
        ]);
    }

    public function privacy()
    {
        return view('pages.privacy', [
            'contentSection' => page_section('privacy', 'content'),
        ]);
    }
}
