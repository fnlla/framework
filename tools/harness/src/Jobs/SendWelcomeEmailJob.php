<?php
/**
 * fnlla - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */


declare(strict_types=1);

namespace App\Jobs;

use Fnlla\Core\Container;
use Fnlla\Mail\Address;
use Fnlla\Mail\Mail;
use Fnlla\Mail\Message;
use Fnlla\Queue\JobInterface;

final class SendWelcomeEmailJob implements JobInterface
{
    public function __construct(private string $email)
    {
    }

    public function handle(Container $app): void
    {
        $message = new Message(
            new Address('no-reply@Fnlla.test', 'Fnlla'),
            [new Address($this->email)],
            'Welcome',
            'Welcome to Fnlla.'
        );

        Mail::send($message);
    }
}
