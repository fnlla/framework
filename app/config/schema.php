<?php

declare(strict_types=1);

return [
    'name' => 'required|string',
    'env' => 'required|string|in:local,dev,development,test,staging,prod,production',
    'version' => 'required|string',
    'timezone' => 'required|string',
    'database.default' => 'required|string|in:sqlite,mysql,pgsql,postgres,postgresql,mariadb',
    'cache.driver' => 'required|string|in:file,array,redis',
    'queue.driver' => 'required|string|in:sync,database,redis',
    'log.format' => 'required|string|in:line,json',
];
