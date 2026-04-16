<?php
/**
 * Finella - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Ai;

use Finella\Core\Container;
use Finella\Database\ConnectionManager;
use Finella\Ai\Policy\AiPolicy;
use Finella\Ai\Policy\AiPolicyClient;
use Finella\Ai\Rag\RagRepository;
use Finella\Ai\Rag\RagService;
use Finella\Ai\Redaction\AiRedactor;
use Finella\Ai\Router\AiRouter;
use Finella\Ai\Skills\AiSkillRegistry;
use Finella\Ai\Telemetry\AiTelemetryRepository;
use Finella\Ai\Telemetry\AiTelemetryService;
use Finella\Support\HttpClient;
use Finella\Support\ServiceProvider;
use RuntimeException;

final class AiServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        $app->singleton(AiManager::class, function () use ($app): AiManager {
            $http = $app->make(HttpClient::class);
            return new AiManager($http, $app->config());
        });

        $app->singleton(AiPolicy::class, function () use ($app): AiPolicy {
            $config = $app->config()->get('ai_policy', []);
            if (!is_array($config)) {
                $config = [];
            }
            return new AiPolicy($config);
        });

        $app->singleton(AiTelemetryRepository::class, function () use ($app): AiTelemetryRepository {
            if (!$app->has(ConnectionManager::class)) {
                throw new RuntimeException('Database connection manager is not available.');
            }
            $config = $app->config()->get('ai_telemetry', []);
            if (!is_array($config)) {
                $config = [];
            }
            $connections = $app->make(ConnectionManager::class);
            return new AiTelemetryRepository($connections, $config);
        });

        $app->singleton(AiTelemetryService::class, function () use ($app): AiTelemetryService {
            $config = $app->config()->get('ai_telemetry', []);
            if (!is_array($config)) {
                $config = [];
            }
            $repo = $app->make(AiTelemetryRepository::class);
            return new AiTelemetryService($repo, $config);
        });

        $app->singleton(AiRedactor::class, function () use ($app): AiRedactor {
            $config = $app->config()->get('ai_redaction', []);
            if (!is_array($config)) {
                $config = [];
            }
            return new AiRedactor($config);
        });

        $app->singleton(AiRouter::class, function () use ($app): AiRouter {
            $config = $app->config()->get('ai_router', []);
            if (!is_array($config)) {
                $config = [];
            }
            return new AiRouter($config);
        });

        $app->singleton(AiSkillRegistry::class, function () use ($app): AiSkillRegistry {
            $config = $app->config()->get('ai_skills', []);
            if (!is_array($config)) {
                $config = [];
            }
            return new AiSkillRegistry($config);
        });

        $app->singleton(AiClientInterface::class, function () use ($app): AiClientInterface {
            $manager = $app->make(AiManager::class);
            $client = $manager->client();
            $policy = $app->make(AiPolicy::class);
            $telemetry = $app->make(AiTelemetryService::class);
            $redactor = $app->make(AiRedactor::class);
            $router = $app->make(AiRouter::class);
            return new AiPolicyClient($client, $policy, $telemetry, $redactor, $router);
        });

        $app->singleton(OpenAiClient::class, function () use ($app): OpenAiClient {
            $manager = $app->make(AiManager::class);
            return $manager->openai();
        });

        $app->singleton(RagRepository::class, function () use ($app): RagRepository {
            if (!$app->has(ConnectionManager::class)) {
                throw new RuntimeException('Database connection manager is not available.');
            }
            $config = $app->config()->get('ai_rag', []);
            if (!is_array($config)) {
                $config = [];
            }
            $connections = $app->make(ConnectionManager::class);
            return new RagRepository($connections, $config);
        });

        $app->singleton(RagService::class, function () use ($app): RagService {
            $config = $app->config()->get('ai_rag', []);
            if (!is_array($config)) {
                $config = [];
            }
            $client = $app->make(AiClientInterface::class);
            $repo = $app->make(RagRepository::class);
            return new RagService($client, $repo, $config);
        });
    }
}


