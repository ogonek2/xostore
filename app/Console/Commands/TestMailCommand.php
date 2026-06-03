<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {email?}';

    protected $description = 'Test SMTP connection and send a test message';

    public function handle(): int
    {
        $to = $this->argument('email') ?? config('mail.from.address');

        $this->line('Mailer: '.config('mail.default'));
        $this->line('Host: '.config('mail.mailers.smtp.host'));
        $this->line('Port: '.config('mail.mailers.smtp.port'));
        $this->line('Scheme: '.(config('mail.mailers.smtp.scheme') ?? 'null'));
        $this->line('Username: ['.config('mail.mailers.smtp.username').']');
        $this->line('From: '.config('mail.from.address'));

        try {
            Mail::raw('XO Store SMTP test '.now()->toDateTimeString(), function ($message) use ($to): void {
                $message->to($to)->subject('SMTP test');
            });

            $this->info('OK — message sent to '.$to);

            return self::SUCCESS;
        } catch (TransportExceptionInterface $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
