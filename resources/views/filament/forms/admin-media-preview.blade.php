@if (! empty($previewUrl))
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
        <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-200">
            Текущее изображение
            @if (! empty($fileName))
                <span class="font-normal text-gray-500 dark:text-gray-400">({{ $fileName }})</span>
            @endif
        </p>
        <a href="{{ $previewUrl }}" target="_blank" rel="noopener noreferrer" class="inline-block max-w-full">
            <img
                src="{{ $previewUrl }}"
                alt=""
                class="max-h-48 w-auto max-w-full rounded-lg border border-gray-200 object-contain dark:border-gray-600"
                loading="lazy"
            >
        </a>
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            Чтобы заменить — в поле ниже удалите файл (иконка корзины) и загрузите новый.
        </p>
    </div>
@endif
