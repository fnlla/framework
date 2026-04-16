<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace App\Controllers;

use Finella\Core\Container;
use Finella\Http\Request;
use Finella\Http\Response;
use Finella\Mail\Address;
use Finella\Mail\Mail;
use Finella\Mail\Message;
use Finella\Queue\JobInterface;
use Finella\Queue\Queue;

final class TestController
{
    public function validate(Request $request): Response
    {
        $request->validate([
            'name' => 'required|string|min:2',
        ]);

        return Response::json(['ok' => true]);
    }

    public function protected(): Response
    {
        return Response::json(['ok' => true]);
    }

    public function mail(): Response
    {
        $message = new Message(
            new Address('from@example.test', 'Finella Test'),
            [new Address('to@example.test', 'Receiver')],
            'Test mail',
            'Hello from Finella.'
        );
        Mail::send($message);

        return Response::json(['ok' => true]);
    }

    public function queue(): Response
    {
        $job = new class implements JobInterface {
            public function handle(Container $app): void
            {
                // no-op for test
            }
        };
        Queue::dispatch($job);

        return Response::json(['ok' => true]);
    }
}
