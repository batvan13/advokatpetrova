<section class="bg-petrova-deep py-16 lg:py-20">
    <div class="mx-auto w-full max-w-[1400px] px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">
            <div class="pt-6 lg:pt-10">
                <p class="font-cormorant text-white text-[21px] tracking-wide mb-3">Адвокатска кантора Петрова</p>
                <h1 class="font-cormorant italic text-petrova-gold text-5xl lg:text-6xl xl:text-[56px] leading-[1.1]">
                    Защитаваме Вашите права.
                    Пазим Вашето бъдеще.
                </h1>
                <a href="{{ route('contacts') }}"
                   class="inline-flex items-center gap-3 mt-6 px-7 py-3.5 rounded-md text-sm font-medium tracking-wide bg-petrova-gold text-petrova-deep hover:opacity-90 transition">
                    Свържете се с нас
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M4 12L12 4M12 4H5M12 4V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
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
                </div>
            </div>
        </div>
    </div>
</section>
