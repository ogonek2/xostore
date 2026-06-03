<?php

namespace App\Services\Newsletter;

use App\Enums\NewsletterCampaignStatus;
use App\Enums\NewsletterSendStatus;
use App\Enums\NewsletterSubscriberStatus;
use App\Mail\NewsletterCampaignMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSend;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class NewsletterCampaignSender
{
    /**
     * @return array{recipients: int, sent: int, failed: int, skipped: int}
     */
    public function sendNow(NewsletterCampaign $campaign): array
    {
        if (! $campaign->canSend()) {
            throw new \RuntimeException('Рассылку можно отправить только из статуса «Черновик».');
        }

        $recipients = $this->resolveRecipients($campaign);

        $campaign->update([
            'started_at' => now(),
            'recipients_count' => $recipients->count(),
            'sent_count' => 0,
            'failed_count' => 0,
            'skipped_count' => 0,
            'created_by' => $campaign->created_by ?? Auth::id(),
        ]);

        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($recipients as $subscriber) {
            $send = NewsletterSend::query()->firstOrCreate(
                [
                    'newsletter_campaign_id' => $campaign->id,
                    'newsletter_subscriber_id' => $subscriber->id,
                ],
                ['status' => NewsletterSendStatus::Pending],
            );

            if (! $subscriber->isMailable()) {
                $send->update([
                    'status' => NewsletterSendStatus::Skipped,
                    'error_message' => 'Подписчик недоступен',
                ]);
                $skipped++;

                continue;
            }

            try {
                Mail::to($subscriber->email)->send(new NewsletterCampaignMail($campaign, $subscriber));

                $send->update([
                    'status' => NewsletterSendStatus::Sent,
                    'sent_at' => now(),
                    'error_message' => null,
                ]);
                $sent++;
            } catch (Throwable $e) {
                $send->update([
                    'status' => NewsletterSendStatus::Failed,
                    'error_message' => Str::limit($e->getMessage(), 500),
                ]);
                $failed++;
            }
        }

        $campaign->update([
            'status' => NewsletterCampaignStatus::Sent,
            'completed_at' => now(),
            'sent_count' => $sent,
            'failed_count' => $failed,
            'skipped_count' => $skipped,
        ]);

        return [
            'recipients' => $recipients->count(),
            'sent' => $sent,
            'failed' => $failed,
            'skipped' => $skipped,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, NewsletterSubscriber>
     */
    protected function resolveRecipients(NewsletterCampaign $campaign)
    {
        $query = NewsletterSubscriber::query()
            ->where('status', NewsletterSubscriberStatus::Subscribed);

        if ($campaign->newsletter_group_id) {
            $query->whereHas('groups', fn ($q) => $q->where('newsletter_groups.id', $campaign->newsletter_group_id));
        }

        return $query->orderBy('id')->get();
    }
}
