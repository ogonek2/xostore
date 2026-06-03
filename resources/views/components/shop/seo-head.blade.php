@props(['seo'])

@php
    /** @var \App\Support\Seo\PageSeo $seo */
    $description = $seo->description ?: config('shop.seo.default_description') ?: __('seo.default_description');
    $canonical = $seo->canonicalUrl();
    $ogImage = $seo->ogImage ?: config('shop.seo.og_image');
@endphp

<title>{{ $seo->documentTitle() }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $seo->robots() }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta property="og:type" content="{{ $seo->ogType }}">
<meta property="og:title" content="{{ $seo->title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:site_name" content="{{ config('shop.name') }}">
@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif

<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo->title }}">
<meta name="twitter:description" content="{{ $description }}">
@if ($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif
