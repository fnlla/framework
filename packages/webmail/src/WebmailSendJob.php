<?php
/**
 * fnlla (finella) - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Webmail;

use Finella\Core\Container;
use Finella\Mail\Address;
use Finella\Mail\Message;
use Finella\Queue\JobInterface;

final class WebmailSendJob implements JobInterface
{
    /**
     * @param array<int, string> $to
     */
    public function __construct(
        private string $from,
        private ?string $fromName,
        private array $to,
        private string $subject,
        private string $text,
        private ?string $html = null
    ) {
    }

    public function handle(Container $app): void
    {
        $smtp = $app->make(WebmailSmtpClient::class);
        if (!$smtp instanceof WebmailSmtpClient) {
            return;
        }

        $from = new Address($this->from, $this->fromName);
        $recipients = [];
        foreach ($this->to as $email) {
            if (is_string($email) && $email !== '') {
                $recipients[] = new Address($email);
            }
        }

        $message = new Message(
            from: $from,
            to: $recipients,
            subject: $this->subject,
            text: $this->text !== '' ? $this->text : null,
            html: $this->html
        );

        $smtp->send($message);
    }
}


