@props(['cards', 'title' => null, 'viewAll' => null])

<div
    data-vue="categories-swiper"
    data-cards='@json($cards)'
    data-title="{{ $title ?? __('shop.categories.title') }}"
    data-view-all="{{ $viewAll ?? __('shop.categories.view_all') }}"
></div>
