<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Queue;

interface QueueInterface
{
    public function dispatch(JobInterface $job): void;
}