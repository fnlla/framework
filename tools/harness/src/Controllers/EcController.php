<?php
/**
 * fnlla
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\Post;
use Fnlla\Auth\AuthManager;
use Fnlla\Http\Request;
use Fnlla\Http\Response;
use Fnlla\Queue\Queue;

final class EcController
{
    public function registerForm(): Response
    {
        $csrf = function_exists('csrf_field') ? csrf_field() : '';
        $html = '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>EC Register</title></head><body>'
            . '<h1>Register</h1><form method="post" action="/_ec/register">'
            . $csrf
            . '<input name="name" placeholder="Name">'
            . '<input name="email" placeholder="Email">'
            . '<input name="password" type="password" placeholder="Password">'
            . '<input name="password_confirmation" type="password" placeholder="Confirm">'
            . '<button type="submit">Register</button></form></body></html>';

        return Response::html($html);
    }

    public function registerSubmit(Request $request): Response
    {
        $data = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $app = app();
        $auth = $app->make(AuthManager::class);
        $user = $auth->register($data);
        $auth->login($user);

        return Response::redirect('/_ec/dashboard');
    }

    public function loginForm(): Response
    {
        $csrf = function_exists('csrf_field') ? csrf_field() : '';
        $html = '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>EC Login</title></head><body>'
            . '<h1>Login</h1><form method="post" action="/_ec/login">'
            . $csrf
            . '<input name="email" placeholder="Email">'
            . '<input name="password" type="password" placeholder="Password">'
            . '<button type="submit">Login</button></form></body></html>';

        return Response::html($html);
    }

    public function loginSubmit(Request $request): Response
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $app = app();
        $auth = $app->make(AuthManager::class);
        $result = $auth->attempt($data, false, $request);
        if (!$result->authenticated) {
            return Response::json(['message' => 'Invalid credentials'], 422);
        }

        return Response::redirect('/_ec/dashboard');
    }

    public function dashboard(): Response
    {
        return Response::json(['ok' => true]);
    }

    public function posts(): Response
    {
        $posts = Post::query()->with('comments')->get();
        $payload = array_map(static function (Post $post): array {
            $data = $post->toArray();
            $comments = [];
            $items = $post->comments ?? [];
            if (is_array($items)) {
                foreach ($items as $comment) {
                    if (is_object($comment) && method_exists($comment, 'toArray')) {
                        $comments[] = $comment->toArray();
                    } else {
                        $comments[] = (array) $comment;
                    }
                }
            }
            $data['comments'] = $comments;
            return $data;
        }, $posts);

        return Response::json(['data' => $payload]);
    }

    public function welcome(): Response
    {
        Queue::dispatch(new SendWelcomeEmailJob('welcome@example.test'));
        return Response::json(['ok' => true]);
    }

    public function obs(): Response
    {
        return Response::json(['ok' => true]);
    }

    public function scheduledMark(): Response
    {
        $root = defined('APP_ROOT') ? APP_ROOT : getcwd();
        $path = rtrim((string) $root, '/\\')
            . DIRECTORY_SEPARATOR . 'storage'
            . DIRECTORY_SEPARATOR . 'cache'
            . DIRECTORY_SEPARATOR . 'ec-scheduled.txt';

        return Response::json(['exists' => is_file($path)]);
    }

    public function back(): Response
    {
        return back();
    }

    public function validate(Request $request): Response
    {
        $request->validate([
            'name' => 'required|string|min:2',
        ]);

        return Response::redirect('/_ec/ok');
    }
}
