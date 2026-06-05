@php
    $result = $result ?? null;
@endphp

@if ($result)
    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Результат импорта</h2>

        <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                <dt class="text-xs uppercase text-gray-500">Создано</dt>
                <dd class="text-xl font-semibold">{{ $result['created'] ?? 0 }}</dd>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                <dt class="text-xs uppercase text-gray-500">Обновлено</dt>
                <dd class="text-xl font-semibold">{{ $result['updated'] ?? 0 }}</dd>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                <dt class="text-xs uppercase text-gray-500">Вариантов</dt>
                <dd class="text-xl font-semibold">+{{ $result['variants_created'] ?? 0 }} / ~{{ $result['variants_updated'] ?? 0 }}</dd>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                <dt class="text-xs uppercase text-gray-500">Пропущено</dt>
                <dd class="text-xl font-semibold">{{ $result['skipped'] ?? 0 }}</dd>
            </div>
        </dl>

        @if (! empty($result['warnings']))
            <div class="mt-4">
                <h3 class="text-sm font-medium text-amber-700 dark:text-amber-300">Предупреждения</h3>
                <ul class="mt-2 max-h-40 list-disc overflow-y-auto pl-5 text-sm text-gray-700 dark:text-gray-200">
                    @foreach ($result['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! empty($result['errors']))
            <div class="mt-4">
                <h3 class="text-sm font-medium text-red-700 dark:text-red-300">Ошибки</h3>
                <ul class="mt-2 max-h-48 list-disc overflow-y-auto pl-5 text-sm text-gray-700 dark:text-gray-200">
                    @foreach ($result['errors'] as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
