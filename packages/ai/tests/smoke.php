<?php

declare(strict_types=1);

require __DIR__ . '/../../_shared/tests/bootstrap.php';

use Finella\Ai\AiClientInterface;
use Finella\Ai\AiManager;
use Finella\Ai\OpenAiClient;
use Finella\Ai\Policy\AiPolicyClient;
use Finella\Core\ConfigRepository;
use Finella\Support\HttpClient;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: $msg\n");
        exit(1);
    }
}

$configRepo = ConfigRepository::fromRoot(getcwd());
$config = $configRepo;
$http = new HttpClient();

$manager = new AiManager($http, $config);
$client = $manager->client();

ok($client instanceof AiClientInterface, 'AiClientInterface resolved');
if ($client instanceof AiPolicyClient) {
    ok($client->inner() instanceof OpenAiClient, 'OpenAiClient resolved');
} else {
    ok($client instanceof OpenAiClient, 'OpenAiClient resolved');
}

echo "AI smoke tests OK\n";
