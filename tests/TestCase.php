<?php

namespace Tests;

use JesseGall\Proxy\Forwarder\Strategies\CallStrategy;
use JesseGall\Proxy\Forwarder\Strategies\Exceptions\ExecutionException;
use JesseGall\Proxy\Interactions\CallInteraction;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Tests\TestClasses\TestException;

class TestCase extends BaseTestCase
{

    protected object $target;

    protected function setUp(): void
    {
        parent::setUp();

        $this->target = new class {
            public int $calls = 0;

            public function method(): void
            {
                $this->calls++;
            }

            public function methodThatThrowsException(): void
            {
                $this->calls++;

                throw new TestException();
            }

        };
    }

    protected function generateException(string $method): ExecutionException
    {
        $interaction = new CallInteraction($this->target, $method, []);

        $strategy = new CallStrategy($interaction);

        return new ExecutionException($strategy, new TestException());
    }

}