<?php

namespace JesseGall\ProxyUtils\Handlers;

use Closure;
use Exception;
use JesseGall\Proxy\Forwarder\Contracts\HandlesFailedExecutions;
use JesseGall\Proxy\Forwarder\Strategies\Exceptions\ExecutionException;

class RetryHandler implements HandlesFailedExecutions
{

    /**
     * The maximum number of times to retry the interaction.
     *
     * @var int
     */
    private int $maxAttempts;

    /**
     * The amount of times the interaction has been retried.
     *
     * @var int
     */
    private int $attempts = 0;

    /**
     * Invoked before each retry to determine if the interaction should be retried.
     *
     * @var Closure|null
     */
    private ?Closure $shouldRetry;

    /**
     * Create a new RetryHandler instance.
     *
     * @param int $maxAttempts
     * @param Closure|null $shouldRetry
     */
    public function __construct(int $maxAttempts, Closure $shouldRetry = null)
    {
        $this->maxAttempts = $maxAttempts;
        $this->shouldRetry = $shouldRetry;
    }

    /**
     * Handle the exception thrown by the execution of a forward strategy.
     *
     * @param ExecutionException $exception
     * @return void
     */
    public function handle(ExecutionException $exception): void
    {
        if (! $this->shouldRetry($exception->getException())) {
            return;
        }

        $this->attempts++;

        try {
            $exception->getStrategy()->execute();

            $exception->setShouldThrow(false);
        } catch (ExecutionException $exception) {
            $this->handle($exception);
        }
    }

    /**
     * Check if the exception should be retried.
     *
     * @param Exception $exception
     * @return bool
     */
    protected function shouldRetry(Exception $exception): bool
    {
        if ($this->attempts >= $this->maxAttempts) {
            return false;
        }

        if ($this->shouldRetry) {
            return (bool)($this->shouldRetry)($exception, $this->attempts);
        }

        return true;
    }

}