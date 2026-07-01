@extends('layouts.shop')

@section('content')
    <x-shop.header
        :navigation="$navigation"
        :languages="$languages"
        :cart-count="$cartCount"
    />

    <main class="flex-1 overflow-x-clip">
        @foreach ($homepageBlocks as $block)
            <x-shop.homepage-block :type="$block['type']" :props="$block['props']" />
        @endforeach
    </main>
@endsection
