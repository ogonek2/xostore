@props([
    'items',
])

@if ($items->isNotEmpty())
    <section class="mx-auto max-w-[90rem] px-5 py-6 lg:px-8 lg:py-8">
        <div class="mb-5 flex items-end justify-between">
            <h2 class="text-xl font-semibold tracking-tight text-primary-DEFAULT lg:text-2xl">
                {{ __('shop.banners.title') }}
            </h2>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($items as $banner)
                <a
                    href="{{ $banner['url'] }}"
                    class="group relative block overflow-hidden bg-[#eceae6]"
                >
                    <img
                        src="{{ $banner['image'] }}"
                        alt="{{ $banner['title'] ?: __('shop.banners.alt') }}"
                        class="aspect-[16/9] w-full object-cover object-center transition duration-500 group-hover:scale-[1.02]"
                        loading="lazy"
                    >
                    @if (! empty($banner['title']))
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/55 to-transparent p-4">
                            <p class="text-sm font-medium text-white lg:text-base">{{ $banner['title'] }}</p>
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
