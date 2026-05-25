<section class="mx-auto max-w-[90rem] px-5 pb-8 pt-6 lg:px-8 lg:pb-10 lg:pt-8">
    <div class="relative overflow-hidden rounded-[2rem] lg:rounded-[2.5rem]">
        <img
            src="{{ asset($image) }}"
            alt=""
            class="aspect-[16/7] h-auto w-full object-cover object-center sm:aspect-[16/6] lg:aspect-[2.4/1]"
            width="1440"
            height="600"
            fetchpriority="high"
        >

        <div class="absolute inset-0 bg-gradient-to-r from-black/25 via-black/10 to-transparent"></div>

        <div class="absolute inset-0 flex items-center">
            <div class="w-full max-w-xl px-8 py-10 sm:px-12 lg:px-16 lg:py-14">
                <p class="mb-3 text-sm font-medium tracking-wide text-white/90 sm:text-base">
                    {{ __('shop.hero.subtitle') }}
                </p>
                <h1 class="mb-8 max-w-md text-3xl font-bold uppercase leading-[1.05] tracking-tight text-white sm:text-4xl lg:text-[2.75rem] lg:leading-[1.08]">
                    {{ __('shop.hero.title') }}
                </h1>
                <a
                    href="{{ $ctaUrl ?? '#' }}"
                    class="inline-flex min-w-[10rem] items-center justify-center bg-white px-8 py-3.5 text-sm font-semibold uppercase tracking-[0.12em] text-primary-DEFAULT transition-colors hover:bg-white/95"
                >
                    {{ __('shop.hero.cta') }}
                </a>
            </div>
        </div>
    </div>
</section>
