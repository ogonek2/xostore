@props(['network' => 'link'])

<i
    @class([
        \App\Enums\SocialNetwork::iconClassFor($network),
        'shop-social-icon',
    ])
    aria-hidden="true"
></i>
