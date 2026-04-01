<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PageSection;

class PageSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'page' => 'home',
                'section' => 'hero',
                'title' => 'Професионални решения за вашия бизнес',
                'subtitle' => 'Изчистен уебсайт, който представя услугите ви по ясен и убедителен начин.',
                'content' => 'Свържете се с нас още днес и разберете как можем да помогнем на вашия бизнес.',
                'meta' => [
                    'button_text' => 'Свържете се с нас',
                    'button_url' => 'contacts',
                ],
            ],
            [
                'page' => 'home',
                'section' => 'services_preview',
                'title' => 'Нашите услуги',
                'subtitle' => 'Кратък преглед на основните услуги, които предлагаме.',
                'content' => null,
                'meta' => [
                    'button_text' => 'Виж всички услуги',
                    'button_url' => 'services',
                ],
            ],
            [
                'page' => 'home',
                'section' => 'about_preview',
                'title' => 'За нас',
                'subtitle' => 'Кратко представяне на бизнеса и неговите предимства.',
                'content' => 'Работим с внимание към всеки детайл и индивидуален подход към всеки клиент.',
                'meta' => [
                    'button_text' => 'Научете повече',
                    'button_url'  => 'about',
                ],
            ],
            [
                'page' => 'home',
                'section' => 'gallery_preview',
                'title' => 'Галерия',
                'subtitle' => 'Снимки и видеа от нашата работа.',
                'content' => 'Поглед към нашата работа — снимки и видеа от реализирани проекти.',
                'meta' => [
                    'button_text' => 'Виж галерията',
                    'button_url' => 'gallery',
                ],
            ],
            [
                'page' => 'home',
                'section' => 'contact_preview',
                'title' => 'Контакти',
                'subtitle' => 'Свържете се с нас за въпроси и запитвания.',
                'content' => 'Готови сме да отговорим на вашите въпроси и да намерим заедно най-доброто решение.',
                'meta' => [
                    'button_text' => 'Към контакти',
                    'button_url' => 'contacts',
                ],
            ],
            [
                'page' => 'home',
                'section' => 'faq',
                'title' => null,
                'subtitle' => null,
                'content' => null,
                'meta' => null,
                'faq' => null,
            ],
            [
                'page' => 'about',
                'section' => 'hero',
                'title' => 'За нашия бизнес',
                'subtitle' => 'Информация за историята, опита и подхода ни.',
                'content' => 'Ние сме екип от специалисти с дългогодишен опит в бранша. Ангажираме се с качество, надеждност и индивидуален подход към всеки клиент.',
                'meta' => [
                    'button_text' => 'Свържете се с нас',
                    'button_url'  => 'contacts',
                ],
            ],
            [
                'page' => 'about',
                'section' => 'content',
                'title' => 'Нашият подход',
                'subtitle' => null,
                'content' => 'Работим с ясна цел — да предложим решения, които реално помагат на бизнеса на нашите клиенти. Опитът, натрупан с годините, ни позволява да предлагаме услуги с гарантирано качество.',
                'meta' => null,
            ],
            [
                'page' => 'services',
                'section' => 'hero',
                'title' => 'Нашите услуги',
                'subtitle' => 'Разгледайте пълния списък с услуги, които предлагаме.',
                'content' => null,
                'meta' => null,
            ],
            [
                'page' => 'gallery',
                'section' => 'hero',
                'title' => 'Галерия',
                'subtitle' => 'Снимки от нашата работа и завършени проекти.',
                'content' => null,
                'meta' => null,
            ],
            [
                'page' => 'contacts',
                'section' => 'hero',
                'title' => 'Свържете се с нас',
                'subtitle' => 'Ще се радваме да отговорим на вашите въпроси.',
                'content' => null,
                'meta' => null,
            ],
            [
                'page' => 'contacts',
                'section' => 'content',
                'title' => null,
                'subtitle' => null,
                'content' => null,
                'meta' => null,
            ],
            [
                'page' => 'consultation',
                'section' => 'hero',
                'title' => 'Платена онлайн консултация',
                'subtitle' => 'Резервирайте удобен час и получете фокусиран отговор от специалист.',
                'content' => 'След заявка ще получите потвърждение и детайли за провеждане на срещата онлайн.',
                'meta' => [
                    'button_text' => 'Към контакти',
                    'button_url' => 'contacts',
                ],
            ],
            [
                'page' => 'consultation',
                'section' => 'content',
                'title' => null,
                'subtitle' => null,
                'content' => null,
                'meta' => null,
            ],
        ];

        foreach ($sections as $section) {
            $values = [
                'title' => $section['title'],
                'subtitle' => $section['subtitle'],
                'content' => $section['content'],
                'meta' => $section['meta'],
            ];
            if (array_key_exists('faq', $section)) {
                $values['faq'] = $section['faq'];
            }

            PageSection::updateOrCreate(
                [
                    'page' => $section['page'],
                    'section' => $section['section'],
                ],
                $values
            );
        }
    }
}
