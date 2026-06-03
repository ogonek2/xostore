<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterCampaign $campaign,
        public NewsletterSubscriber $subscriber,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->renderBody(),
        );
    }

    protected function renderBody(): string
    {
        $unsubscribeUrl = route('newsletter.unsubscribe', [
            'token' => $this->subscriber->unsubscribe_token,
        ]);

        $replacements = [
            '{{name}}' => e($this->subscriber->name ?? ''),
            '{{email}}' => e($this->subscriber->email),
            '{{unsubscribe_url}}' => $unsubscribeUrl,
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $this->campaign->body_html);

        return view('emails.newsletter.campaign', [
            'body' => $html,
            'unsubscribeUrl' => $unsubscribeUrl,
        ])->render();
    }
}
