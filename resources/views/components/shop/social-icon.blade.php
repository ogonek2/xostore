@props(['network' => 'link'])

@switch($network)
    @case('instagram')
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="5" />
            <circle cx="12" cy="12" r="4" />
            <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" stroke="none" />
        </svg>
        @break
    @case('facebook')
        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M14 8.5V6.75c0-.69.56-1.25 1.25-1.25H17V3h-2.25A3.75 3.75 0 0011 6.75V8.5H9v3h2V21h3v-9.5h2.25L17 11h-3z" />
        </svg>
        @break
    @case('pinterest')
        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 3C7.03 3 3 7.03 3 12c0 4.17 2.52 7.75 6.13 9.28-.09-.79-.17-2 .03-2.84.18-.77 1.17-4.91 1.17-4.91s-.3-.6-.3-1.49c0-1.39.81-2.43 1.81-2.43.86 0 1.27.64 1.27 1.41 0 .86-.55 2.15-.83 3.35-.24 1 .5 1.82 1.48 1.82 1.78 0 3.15-1.88 3.15-4.58 0-2.39-1.72-4.06-4.18-4.06-2.85 0-4.52 2.14-4.52 4.35 0 .86.33 1.78.74 2.28a.3.3 0 01-.07.28l-.27 1.09c-.04.17-.13.21-.3.13-1.12-.52-1.82-2.15-1.82-3.47 0-2.82 2.05-5.41 5.91-5.41 3.1 0 5.51 2.21 5.51 5.17 0 3.08-1.94 5.56-4.64 5.56-.91 0-1.76-.47-2.05-1.03l-.56 2.14c-.2.78-.75 1.75-1.12 2.35.84.26 1.73.4 2.65.4 5.52 0 10-4.48 10-10S17.52 3 12 3z" />
        </svg>
        @break
    @default
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
        </svg>
@endswitch
