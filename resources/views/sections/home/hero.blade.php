<section class="bg-petrova-deep py-16 lg:py-20">
    <div class="mx-auto w-full max-w-[1400px] px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">
            <div class="pt-6 lg:pt-10">
                <h1 class="font-playfair italic text-petrova-gold text-5xl lg:text-6xl xl:text-[64px] leading-[1.1] mb-6">
                    Защитаваме Вашите права.<br>
                    Пазим Вашето бъдеще.
                </h1>
                <p class="text-white/70 text-sm mb-4">
                    Екипът на Адвокатска кантора Петрова предлага:
                </p>
                <ul class="list-disc list-inside space-y-2 text-sm text-white/70 mb-8">
                    <li>Безплатна адвокатска помощ при случаи на домашно насилие</li>
                    <li>Безплатни консултации за материално затруднени лица всеки четвъртък, от 11:00 ч. до 12:00 ч.</li>
                </ul>
                <a
                    href="{{ route('contacts') }}"
                    class="inline-flex items-center justify-between bg-petrova-gold text-petrova-deep text-sm font-semibold px-8 py-3.5 min-w-[280px] hover:bg-petrova-gold-hover transition no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/60 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep"
                >
                    <span class="shrink-0 whitespace-nowrap">Свържете се с нас</span>
                    <svg class="shrink-0 ml-4" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M1 11L11 1M11 1H3M11 1V9" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </a>
            </div>
            <div class="w-full">
                <div class="overflow-hidden rounded-tl-[10px] rounded-tr-[220px] rounded-br-[10px] rounded-bl-[10px]">
                    <img
                        src="{{ asset('images/home/hero.webp') }}"
                        alt="Hero"
                        class="w-full h-[420px] lg:h-[500px] object-cover"
                        width="800"
                        height="500"
                        loading="eager"
                    >
                </div>
            </div>
        </div>

        <div class="border-t border-white/10 mt-10 py-6">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                <div class="flex flex-wrap gap-2.5">
                    <span class="inline-flex items-center rounded-full border border-white/30 text-white/70 text-xs px-4 py-1.5">Бизнес и сделки</span>
                    <span class="inline-flex items-center rounded-full border border-white/30 text-white/70 text-xs px-4 py-1.5">Имоти и строителство</span>
                    <span class="inline-flex items-center rounded-full border border-white/30 text-white/70 text-xs px-4 py-1.5">Регулации и защита</span>
                    <span class="inline-flex items-center rounded-full border border-white/30 text-white/70 text-xs px-4 py-1.5">Съдебни спорове</span>
                </div>
                <div class="flex items-center gap-4">
                    <p class="text-white/60 text-xs max-w-[280px] text-right">
                        Ние уважаваме и изслушваме внимателно нашите клиенти, подхождаме с ангажираност и искрена грижа.
                    </p>
                    <a href="#">
                        <img
                            src="{{ asset('images/shared/Back to TOP - Button.svg') }}"
                            alt="Back to top"
                            class="w-12 h-12 flex-shrink-0"
                        >
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
