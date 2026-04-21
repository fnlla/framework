<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace App\Controllers;

use Fnlla\\Core\Container;
use Fnlla\\Http\Request;
use Fnlla\\Http\Response;
use Fnlla\\Mail\Address;
use Fnlla\\Mail\Mail;
use Fnlla\\Mail\Message;
use Fnlla\\Queue\JobInterface;
use Fnlla\\Queue\Queue;

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
            new Address('from@example.test', 'Fnlla Test'),
            [new Address('to@example.test', 'Receiver')],
            'Test mail',
            'Hello from Fnlla.'
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
