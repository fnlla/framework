<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Finella\Support\Env;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

function clearEnv(string $key): void
{
    unset($_ENV[$key], $_SERVER[$key]);
    putenv($key);
}

clearEnv('FINELLA_ENV_TEST');
ok(Env::get('FINELLA_ENV_TEST', 'default') === 'default', 'default value should be returned when key missing');

$_ENV['FINELLA_ENV_TEST'] = 'env';
$_SERVER['FINELLA_ENV_TEST'] = 'server';
putenv('FINELLA_ENV_TEST=getenv');
ok(Env::get('FINELLA_ENV_TEST') === 'env', '$_ENV should take precedence');

unset($_ENV['FINELLA_ENV_TEST']);
ok(Env::get('FINELLA_ENV_TEST') === 'server', '$_SERVER should take precedence over getenv');

unset($_SERVER['FINELLA_ENV_TEST']);
ok(Env::get('FINELLA_ENV_TEST') === 'getenv', 'getenv should be used when arrays are empty');

$_ENV['FINELLA_ENV_TRUE'] = 'true';
$_ENV['FINELLA_ENV_FALSE'] = '(false)';
$_ENV['FINELLA_ENV_NULL'] = 'null';
$_ENV['FINELLA_ENV_EMPTY'] = '(empty)';
$_ENV['FINELLA_ENV_ZERO'] = '0';
$_ENV['FINELLA_ENV_TEXT'] = 'some=value';

ok(Env::get('FINELLA_ENV_TRUE') === true, 'true should cast to boolean true');
ok(Env::get('FINELLA_ENV_FALSE') === false, 'false should cast to boolean false');
ok(Env::get('FINELLA_ENV_NULL') === null, 'null should cast to null');
ok(Env::get('FINELLA_ENV_EMPTY') === '', 'empty should cast to empty string');
ok(Env::get('FINELLA_ENV_ZERO') === '0', '0 should remain a string');
ok(Env::get('FINELLA_ENV_TEXT') === 'some=value', 'values with = should be preserved');

clearEnv('FINELLA_ENV_TEST');
clearEnv('FINELLA_ENV_TRUE');
clearEnv('FINELLA_ENV_FALSE');
clearEnv('FINELLA_ENV_NULL');
clearEnv('FINELLA_ENV_EMPTY');
clearEnv('FINELLA_ENV_ZERO');
clearEnv('FINELLA_ENV_TEXT');

echo "OK\n";
