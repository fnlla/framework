<?php

declare(strict_types=1);

use Tests\Feature\Support\FeatureTestCase;

final class DocsAccessFlowTest extends FeatureTestCase
{
    public function testDocsRoutesAreDisabledWhenDocsFeatureIsOff(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValue('DOCS_ENABLED', '0');

        $this->get('/docs')->assertStatus(404);
        $this->get('/docs/getting-started')->assertStatus(404);
    }

    public function testDocsAreAccessibleInDevWithoutToken(): void
    {
        $this->setEnvValues([
            'APP_ENV' => 'dev',
            'APP_DEBUG' => '1',
            'DOCS_ENABLED' => '1',
            'DOCS_PUBLIC' => '0',
        ]);
        $this->unsetEnvValue('DOCS_ACCESS_TOKEN');

        $this->get('/docs/getting-started')->assertStatus(200);
    }

    public function testDocsRequireTokenInPrivateProdMode(): void
    {
        $this->configurePrivateProdDocs();
        $this->get('/docs/getting-started')->assertStatus(404);
    }

    public function testDocsAcceptValidQueryTokenInPrivateProdMode(): void
    {
        $this->configurePrivateProdDocs();
        $this->get('/docs/getting-started?docs_token=release-secret')->assertStatus(200);
    }

    public function testDocsRejectInvalidQueryTokenInPrivateProdMode(): void
    {
        $this->configurePrivateProdDocs();
        $this->get('/docs/getting-started?docs_token=wrong')->assertStatus(404);
    }

    public function testDocsAcceptValidHeaderTokenInPrivateProdMode(): void
    {
        $this->configurePrivateProdDocs();
        $this->get('/docs/getting-started', [
            'X-Docs-Token' => 'release-secret',
        ])->assertStatus(200);
    }

    public function testDocsRejectInvalidHeaderTokenInPrivateProdMode(): void
    {
        $this->configurePrivateProdDocs();
        $this->get('/docs/getting-started', [
            'X-Docs-Token' => 'invalid-token',
        ])->assertStatus(404);
    }

    public function testDocsReturnNotFoundWhenTokenIsNotConfiguredInProdPrivateMode(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValues([
            'DOCS_ENABLED' => '1',
            'DOCS_PUBLIC' => '0',
        ]);
        $this->unsetEnvValue('DOCS_ACCESS_TOKEN');

        $this->get('/docs/getting-started')->assertStatus(404);
    }

    public function testDocsArePublicWhenFlagIsEnabledInProd(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValues([
            'DOCS_ENABLED' => '1',
            'DOCS_PUBLIC' => '1',
            'DOCS_ACCESS_TOKEN' => 'release-secret',
        ]);

        $this->get('/docs/getting-started')->assertStatus(200);
    }

    public function testDocsUnknownSlugReturnsNotFoundEvenWithValidToken(): void
    {
        $this->configurePrivateProdDocs();
        $this->get('/docs/this-page-does-not-exist?docs_token=release-secret')->assertStatus(404);
    }

    private function configurePrivateProdDocs(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValues([
            'DOCS_ENABLED' => '1',
            'DOCS_PUBLIC' => '0',
            'DOCS_ACCESS_TOKEN' => 'release-secret',
        ]);
    }
}
