<?php

namespace App\Services\Newsletter;

use App\Enums\NewsletterSubscriberStatus;
use App\Models\NewsletterGroup;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Str;

class NewsletterSubscriptionService
{
    /**
     * @return array{subscriber: NewsletterSubscriber, created: bool, reactivated: bool}
     */
    public function subscribe(
        string $email,
        ?string $locale = null,
        ?string $name = null,
        string $source = 'website',
        ?string $groupSlug = null,
    ): array {
        $email = Str::lower(trim($email));
        $locale = $locale ?: config('shop.default_language', 'pl');

        $subscriber = NewsletterSubscriber::query()->where('email', $email)->first();
        $created = false;
        $reactivated = false;

        if (! $subscriber) {
            $subscriber = NewsletterSubscriber::query()->create([
                'email' => $email,
                'name' => $name ? trim($name) : null,
                'locale' => $locale,
                'status' => NewsletterSubscriberStatus::Subscribed,
                'source' => $source,
                'subscribed_at' => now(),
            ]);
            $created = true;
        } else {
            if ($subscriber->status !== NewsletterSubscriberStatus::Subscribed) {
                $subscriber->update([
                    'status' => NewsletterSubscriberStatus::Subscribed,
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                ]);
                $reactivated = true;
            }

            if ($name && ! $subscriber->name) {
                $subscriber->update(['name' => trim($name)]);
            }

            if (! $subscriber->locale) {
                $subscriber->update(['locale' => $locale]);
            }
        }

        $this->attachDefaultGroup($subscriber, $groupSlug);

        return [
            'subscriber' => $subscriber->fresh(['groups']),
            'created' => $created,
            'reactivated' => $reactivated,
        ];
    }

    public function unsubscribe(string $token): ?NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::query()
            ->where('unsubscribe_token', $token)
            ->first();

        if (! $subscriber) {
            return null;
        }

        if ($subscriber->status === NewsletterSubscriberStatus::Subscribed) {
            $subscriber->update([
                'status' => NewsletterSubscriberStatus::Unsubscribed,
                'unsubscribed_at' => now(),
            ]);
        }

        return $subscriber->fresh();
    }

    protected function attachDefaultGroup(NewsletterSubscriber $subscriber, ?string $groupSlug): void
    {
        $slug = $groupSlug ?: config('shop.newsletter.default_group_slug');

        if (! $slug) {
            return;
        }

        $group = NewsletterGroup::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if ($group) {
            $subscriber->groups()->syncWithoutDetaching([$group->id]);
        }
    }
}
