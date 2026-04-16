<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$requiredFiles = [
    [$root . '/packages/ai/src/OpenAiClient.php'],
    [$root . '/framework/src/Console/Commands/AiDoctorCommand.php'],
    [$root . '/framework/src/Console/Commands/AiConfigAdvisorCommand.php'],
    [$root . '/framework/src/Console/Commands/AiSecurityLintCommand.php'],
    [$root . '/app/config/ai/ai.php', $root . '/app/config/ai.php'],
    [$root . '/app/config/ai/policy.php', $root . '/app/config/ai_policy.php'],
];

$missing = [];
foreach ($requiredFiles as $group) {
    $found = false;
    foreach ($group as $file) {
        if (is_file($file)) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $missing[] = implode(' OR ', $group);
    }
}

if ($missing !== []) {
    fwrite(STDERR, "Missing AI files: " . implode(', ', $missing) . "\n");
    exit(1);
}

echo "AI smoke tests OK\n";

