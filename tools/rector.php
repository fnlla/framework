<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Php80\Rector\FileWithoutNamespace\FileWithStrictTypesRector;
use Rector\Strict\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\Php80\Rector\ClassMethod\ClassMethodAssignsToPropertyRector;
use Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector;
use Rector\Php74\Rector\Class_\PropertyTypeDeclarationRector;
use Rector\Php80\Rector\ClassMethod\ClassMethodReturnTypeFromStrictScalarReturnsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/../framework/src',
        __DIR__ . '/../packages',
        __DIR__ . '/harness/app',
        __DIR__ . '/harness/bootstrap',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/../packages/_package-template',
        __DIR__ . '/../packages/*/resources',
        __DIR__ . '/../packages/*/tests',
        __DIR__ . '/../ui/stubs',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_83,
    ]);

    $rectorConfig->rules([
        FileWithStrictTypesRector::class,
        PropertyTypeDeclarationRector::class,
        ClassMethodReturnTypeFromStrictScalarReturnsRector::class,
        ReturnTypeFromStrictNativeCallRector::class,
        ClassMethodAssignsToPropertyRector::class,
        ImportFullyQualifiedNamesRector::class,
    ]);
};

