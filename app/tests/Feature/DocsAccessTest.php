<?php

declare(strict_types=1);

use Finella\Testing\TestCase;

final class DocsAccessTest extends TestCase
{
    public function testDocsAccessRequiresTokenInProd(): void
    {
        $this->setEnvValue('APP_ENV', 'prod');
        $this->setEnvValue('APP_DEBUG', '0');
        $this->setEnvValue('DOCS_ENABLED', '1');
        $this->setEnvValue('DOCS_PUBLIC', '0');
        $this->setEnvValue('DOCS_ACCESS_TOKEN', 'release-secret');

        $this->get('/docs/getting-started')->assertStatus(404);
        $this->get('/docs/getting-started?docs_token=release-secret')->assertStatus(200);
    }

    private function setEnvValue(string $key, string $value): void
    {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
