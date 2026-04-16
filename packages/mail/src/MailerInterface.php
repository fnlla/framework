<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Mail;

interface MailerInterface
{
    public function send(Message $msg): void;
}