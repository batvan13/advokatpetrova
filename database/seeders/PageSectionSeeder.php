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

        $legalContentSections = [
            [
                'page' => 'practice',
                'section' => 'content',
                'title' => 'Моята практика',
                'subtitle' => null,
                'content' => '<p>Тук можете да публикувате представяне на вашата практика — опит, специализация, философия на работа и какво могат да очакват клиентите.</p><p>Съдържанието на тази страница подлежи на актуализация според нуждите на сайта и изискванията на клиента.</p>',
                'meta' => null,
            ],
            [
                'page' => 'terms',
                'section' => 'content',
                'title' => 'Общи условия',
                'subtitle' => null,
                'content' => '<p>Тази страница е предназначена за публикуване на общите условия за ползване на сайта и свързаните услуги. Текстът следва да бъде прегледан и приет от правен съветник преди пускане в експлоатация.</p><p>За въпроси, свързани с условията или услугите, моля използвайте страницата за контакти на сайта.</p>',
                'meta' => null,
            ],
            [
                'page' => 'cookie_policy',
                'section' => 'content',
                'title' => 'Политика за бисквитки',
                'subtitle' => null,
                'content' => '<p>Този сайт може да използва бисквитки и подобни технологии, за да работи коректно, да запомни предпочитания или да анализира трафик, в съответствие с приложимото законодателство и вашите настройки.</p><p>При първо посещение обикновено се показва информационен банер, чрез който можете да управлявате съгласието си. Подробно описание на категориите бисквитки, сроковете им и трети страни следва да бъде добавено тук след финализиране на политиката.</p>',
                'meta' => null,
            ],
            [
                'page' => 'privacy',
                'section' => 'content',
                'title' => 'Политика за поверителност',
                'subtitle' => null,
                'content' => '<p>Тази страница описва как се събират, използват и защитават личните данни при посещение на сайта и при заявяване на услуги. Краен текст следва да бъде съгласуван с приложимото право (вкл. Регламент (ЕС) 2016/679) и вашите бизнес процеси.</p><p>При нужда от достъп, корекция или изтриване на данни, както и за подаване на жалба, посочете ясни контактни данни на администратора (напр. чрез страницата за контакти).</p>',
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

        // home.about_preview: insert defaults only when the row is missing — re-seed must not wipe CMS text/meta edits.
        PageSection::firstOrCreate(
            [
                'page' => 'home',
                'section' => 'about_preview',
            ],
            [
                'title' => null,
                'subtitle' => null,
                'content' => null,
                'meta' => [
                    'button_text' => 'Научете повече',
                    'button_url'  => 'about',
                ],
            ]
        );

        PageSection::firstOrCreate(
            [
                'page' => 'about',
                'section' => 'team',
            ],
            [
                'title' => 'Екип',
                'subtitle' => null,
                'content' => 'Запознайте се с част от екипа на кантората.',
                'meta' => null,
            ]
        );

        foreach ($legalContentSections as $section) {
            PageSection::firstOrCreate(
                [
                    'page' => $section['page'],
                    'section' => $section['section'],
                ],
                [
                    'title' => $section['title'],
                    'subtitle' => $section['subtitle'],
                    'content' => $section['content'],
                    'meta' => $section['meta'],
                ]
            );
        }
    }
}
