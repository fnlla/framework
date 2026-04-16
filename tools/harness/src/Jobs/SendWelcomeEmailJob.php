<?php
/**
 * Finella - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */


declare(strict_types=1);

namespace App\Jobs;

use Finella\Core\Container;
use Finella\Mail\Address;
use Finella\Mail\Mail;
use Finella\Mail\Message;
use Finella\Queue\JobInterface;

final class SendWelcomeEmailJob implements JobInterface
{
    public function __construct(private string $email)
    {
    }

    public function handle(Container $app): void
    {
        $message = new Message(
            new Address('no-reply@finella.test', 'Finella'),
            [new Address($this->email)],
            'Welcome',
            'Welcome to Finella.'
        );

        Mail::send($message);
    }
}
