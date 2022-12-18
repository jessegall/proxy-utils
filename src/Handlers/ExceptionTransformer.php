<?php

namespace JesseGall\ProxyUtils\Handlers;

use Closure;
use Exception;
use JesseGall\Proxy\Forwarder\Contracts\HandlesFailedExecutions;
use JesseGall\Proxy\Forwarder\Strategies\Exceptions\ExecutionException;

class ExceptionTransformer implements HandlesFailedExecutions
{

    /**
     * The mapping of exceptions to their transformed versions.
     *
     * @var array<class-string<Exception>, Closure>
     */
    private array $transformations;

    /**
     * Create a new ExceptionTransformer instance.
     *
     * @param array<class-string<Exception>, Closure> $transformations
     */
    public function __construct(array $transformations = [])
    {
        $this->transformations = $transformations;
    }

    /**
     * Handle the exception thrown by the execution of a forward strategy.
     *
     * @param ExecutionException $exception
     * @return void
     */
    public function handle(ExecutionException $exception): void
    {
        if (! $this->shouldTransform($exception->getException())) {
            return;
        }

        $transformed = $this->transform($exception);

        $exception->setException($transformed);
    }

    /**
     * Add a transformation to the transformer.
     *
     * @param string $exception
     * @param Closure $transformation
     * @return $this
     */
    public function addTransformation(string $exception, Closure $transformation): self
    {
        $this->transformations[$exception] = $transformation;

        return $this;
    }

    /**
     * Get the transformations.
     *
     * @return array<class-string<Exception>, Closure>
     */
    public function getTransformations(): array
    {
        return $this->transformations;
    }

    /**
     * Determine if the given exception should be transformed.
     *
     * @param Exception $original
     * @return bool
     */
    private function shouldTransform(Exception $original): bool
    {
        return array_key_exists(get_class($original), $this->transformations);
    }

    /**
     * Transform the given exception.
     *
     * @param ExecutionException $exception
     * @return Exception
     */
    private function transform(ExecutionException $exception): Exception
    {
        return $this->transformations[get_class($exception->getException())]($exception);
    }


}