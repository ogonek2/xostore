<?php

namespace App\Services\Newsletter;

use App\Enums\NewsletterSubscriberStatus;
use App\Models\NewsletterGroup;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscriberCsv
{
    /**
     * @return array{imported: int, updated: int, skipped: int, errors: list<string>}
     */
    public function import(UploadedFile $file, bool $attachDefaultGroup = true): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => ['Не удалось открыть файл']];
        }

        $header = null;
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $line = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;

            if ($header === null) {
                $header = $this->normalizeHeader($row);

                continue;
            }

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $data = $this->mapRow($header, $row);
            $email = Str::lower(trim((string) ($data['email'] ?? '')));

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Строка {$line}: неверный email";
                $skipped++;

                continue;
            }

            try {
                DB::transaction(function () use ($email, $data, $attachDefaultGroup, &$imported, &$updated): void {
                    $status = $this->parseStatus($data['status'] ?? 'subscribed');
                    $subscriber = NewsletterSubscriber::query()->where('email', $email)->first();

                    $attributes = [
                        'name' => filled($data['name'] ?? null) ? trim($data['name']) : null,
                        'locale' => filled($data['locale'] ?? null) ? trim($data['locale']) : null,
                        'status' => $status,
                        'source' => filled($data['source'] ?? null) ? trim($data['source']) : 'import',
                    ];

                    if ($status === NewsletterSubscriberStatus::Subscribed) {
                        $attributes['subscribed_at'] = now();
                        $attributes['unsubscribed_at'] = null;
                    } elseif ($status === NewsletterSubscriberStatus::Unsubscribed) {
                        $attributes['unsubscribed_at'] = now();
                    }

                    if (! $subscriber) {
                        $subscriber = NewsletterSubscriber::query()->create(array_merge($attributes, [
                            'email' => $email,
                        ]));
                        $imported++;
                    } else {
                        $subscriber->update($attributes);
                        $updated++;
                    }

                    $groupSlugs = $this->parseGroupSlugs($data['groups'] ?? '');
                    if ($groupSlugs !== []) {
                        $groupIds = NewsletterGroup::query()
                            ->whereIn('slug', $groupSlugs)
                            ->pluck('id');
                        $subscriber->groups()->syncWithoutDetaching($groupIds);
                    } elseif ($attachDefaultGroup) {
                        $defaultSlug = config('shop.newsletter.default_group_slug');

                        if ($defaultSlug) {
                            $defaultGroupId = NewsletterGroup::query()
                                ->where('slug', $defaultSlug)
                                ->value('id');

                            if ($defaultGroupId) {
                                $subscriber->groups()->syncWithoutDetaching([$defaultGroupId]);
                            }
                        }
                    }
                });
            } catch (\Throwable $e) {
                $errors[] = "Строка {$line}: {$e->getMessage()}";
                $skipped++;
            }
        }

        fclose($handle);

        return compact('imported', 'updated', 'skipped', 'errors');
    }

    public function export(?Collection $records = null): StreamedResponse
    {
        $query = $records ?? NewsletterSubscriber::query()->with('groups')->orderBy('email');

        $filename = 'newsletter-subscribers-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['email', 'name', 'locale', 'status', 'source', 'groups', 'subscribed_at', 'unsubscribed_at']);

            $records = $query instanceof Collection
                ? $query
                : $query->cursor();

            foreach ($records as $subscriber) {
                fputcsv($handle, [
                    $subscriber->email,
                    $subscriber->name ?? '',
                    $subscriber->locale ?? '',
                    $subscriber->status?->value ?? $subscriber->status,
                    $subscriber->source ?? '',
                    $subscriber->groups->pluck('slug')->implode('|'),
                    $subscriber->subscribed_at?->toDateTimeString() ?? '',
                    $subscriber->unsubscribed_at?->toDateTimeString() ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  list<string>  $row
     * @return list<string>
     */
    protected function normalizeHeader(array $row): array
    {
        return array_map(
            fn ($cell) => Str::snake(Str::lower(trim((string) $cell))),
            $row
        );
    }

    /**
     * @param  list<string>  $header
     * @param  list<string>  $row
     * @return array<string, string>
     */
    protected function mapRow(array $header, array $row): array
    {
        $data = [];

        foreach ($header as $index => $key) {
            $data[$key] = trim((string) ($row[$index] ?? ''));
        }

        return $data;
    }

    /**
     * @param  list<string>  $row
     */
    protected function rowIsEmpty(array $row): bool
    {
        return collect($row)->filter(fn ($v) => trim((string) $v) !== '')->isEmpty();
    }

    protected function parseStatus(string $value): NewsletterSubscriberStatus
    {
        $value = Str::lower(trim($value));

        return NewsletterSubscriberStatus::tryFrom($value)
            ?? NewsletterSubscriberStatus::Subscribed;
    }

    /**
     * @return list<string>
     */
    protected function parseGroupSlugs(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        return collect(preg_split('/[|,;]/', $value) ?: [])
            ->map(fn ($slug) => Str::slug(trim($slug)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
