<?php

declare(strict_types=1);

use Fnlla\Core\Container;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    public function testAutowireResolvesDependencies(): void
    {
        $container = new Container();
        $resolved = $container->make(FixtureClass::class);

        $this->assertInstanceOf(FixtureClass::class, $resolved);
        $this->assertInstanceOf(FixtureDep::class, $resolved->dep);
    }

    public function testParameterOverrideByName(): void
    {
        $container = new Container();
        $resolved = $container->make(FixtureWithScalar::class, ['name' => 'custom']);

        $this->assertSame('custom', $resolved->name);
    }

    public function testInterfaceDefaultIsUsedWhenNotBound(): void
    {
        $container = new Container();
        $resolved = $container->make(FixtureWithInterfaceDefault::class);

        $this->assertSame('default', $resolved->value);
    }
}

final class FixtureDep
{
}

final class FixtureClass
{
    public function __construct(public FixtureDep $dep)
    {
    }
}

final class FixtureWithScalar
{
    public function __construct(public string $name = 'default')
    {
    }
}

interface FixtureContract
{
    public function value(): string;
}

final class FixtureWithInterfaceDefault
{
    public string $value;

    public function __construct(?FixtureContract $contract = null)
    {
        $this->value = $contract instanceof FixtureContract ? $contract->value() : 'default';
    }
}
