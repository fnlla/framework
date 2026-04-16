<?php

declare(strict_types=1);

use Finella\Testing\TestCase;

final class EC0RedirectTest extends TestCase
{
    protected bool $useDatabase = false;

    public function testBackRedirectUsesSafeReferer(): void
    {
        $this->get('/_ec/back', ['Referer' => '/ok'])
            ->assertRedirect('/ok');

        $this->get('/_ec/back', ['Referer' => 'https://evil.com'])
            ->assertRedirect('/');
    }

    public function testValidationRedirectUsesSafeReferer(): void
    {
        $this->withCsrf();

        $this->post('/_ec/validate', [], ['Referer' => '/ok'])
            ->assertRedirect('/ok');

        $this->post('/_ec/validate', [], ['Referer' => 'https://evil.com'])
            ->assertRedirect('/');
    }
}
