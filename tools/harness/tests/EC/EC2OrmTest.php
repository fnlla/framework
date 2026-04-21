<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\Comment;
use Fnlla\Testing\TestCase;

final class EC2OrmTest extends TestCase
{
    public function testPostsEndpointReturnsComments(): void
    {
        $post = Post::create([
            'title' => 'Hello EC2',
            'body' => 'Body',
        ]);

        Comment::create([
            'post_id' => $post->id,
            'body' => 'First comment',
        ]);

        $this->get('/_ec/posts')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'title' => 'Hello EC2',
                        'comments' => [
                            ['body' => 'First comment'],
                        ],
                    ],
                ],
            ]);
    }
}
