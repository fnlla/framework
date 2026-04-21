<?php

/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Support;

use Finella\Contracts\Events\EventDispatcherInterface;
use Finella\Core\Container;

final class EventsServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        $app->singleton(EventDispatcher::class, function () use ($app): EventDispatcher {
            $dispatcher = new EventDispatcher($app);
            $this->attachAfterCommitHandler($dispatcher, $app);
            return $dispatcher;
        });

        $app->singleton(
            EventDispatcherInterface::class,
            fn (): EventDispatcherInterface => $app->make(EventDispatcher::class)
        );
    }

    public function boot(Container $app): void
    {
        $dispatcher = $app->make(EventDispatcher::class);
        if ($dispatcher instanceof EventDispatcher) {
            $this->attachAfterCommitHandler($dispatcher, $app);
            $this->registerConfigListeners($dispatcher, $app);
        }
    }

    private function attachAfterCommitHandler(EventDispatcher $dispatcher, Container $app): void
    {
        if (!$app->has(\Finella\Database\TransactionManager::class)) {
            return;
        }

        $manager = $app->make(\Finella\Database\TransactionManager::class);
        if ($manager instanceof \Finella\Database\TransactionManager) {
            $dispatcher->setAfterCommitHandler([$manager, 'afterCommit']);
        }
    }

    private function registerConfigListeners(EventDispatcher $dispatcher, Container $app): void
    {
        if (!method_exists($app, 'config')) {
            return;
        }

        $config = $app->config()->get('events', []);
        if (!is_array($config)) {
            return;
        }

        $listeners = $config['listeners'] ?? $config;
        if (!is_array($listeners)) {
            return;
        }

        foreach ($listeners as $event => $eventListeners) {
            if (!is_string($event) || $event === '') {
                continue;
            }

            if (!is_array($eventListeners)) {
                $eventListeners = [$eventListeners];
            }

            foreach ($eventListeners as $listener) {
                if (is_callable($listener) || (is_string($listener) && $listener !== '')) {
                    $dispatcher->listen($event, $listener);
                }
            }
        }
    }
}







