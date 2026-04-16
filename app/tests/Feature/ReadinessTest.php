<?php

declare(strict_types=1);

use Finella\Testing\TestCase;

final class ReadinessTest extends TestCase
{
    public function testReadyReturnsOkJson(): void
    {
        $this->get('/ready?format=json')
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);
    }
}
