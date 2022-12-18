<?php

namespace Tests;

use Exception;
use JesseGall\ProxyUtils\Handlers\RetryHandler;
use Tests\TestClasses\TestException;

class RetryHandlerTest extends TestCase
{

    public function test__When_handle__Then_interaction_executed_maxAttempt_times()
    {
        $maxAttempts = 5;

        $handler = new RetryHandler($maxAttempts);

        $handler->handle($this->generateException('methodThatThrowsException'));

        $this->assertEquals($maxAttempts, $this->target->calls);
    }

    public function test__Given_closure_returns_false__When_handle__Then_interaction_not_executed_again()
    {
        $handler = new RetryHandler(5, function () {
            return false;
        });

        $handler->handle($this->generateException('methodThatThrowsException'));

        $this->assertEquals(0, $this->target->calls);
    }

    public function test__Given_closure__When_handle__Then_closure_invoked_with_thrown_exception()
    {
        $exception = null;

        $handler = new RetryHandler(5, function (Exception $e) use (&$exception) {
            $exception = $e;
        });

        $handler->handle($this->generateException('methodThatThrowsException'));

        $this->assertInstanceOf(TestException::class, $exception);
    }

    public function test__Given_closure__When_handle__Then_closure_invoked_with_attempt_number()
    {
        $attempt = null;

        $handler = new RetryHandler(5, function (Exception $e, int $a) use (&$attempt) {
            $attempt = $a;

            return $attempt <= 3;
        });

        $handler->handle($this->generateException('methodThatThrowsException'));

        $this->assertEquals(4, $attempt);
    }

    public function test__Given_execution_successful__When_handle__Then_shouldThrow_false()
    {
        $handler = new RetryHandler(5);

        $exception = $this->generateException('method');

        $handler->handle($exception);

        $this->assertFalse($exception->shouldThrow());
    }

    public function test__Given_execution_successful__When_handle__Then_interaction_not_executed_again()
    {
        $handler = new RetryHandler(5);

        $handler->handle($this->generateException('method'));

        $this->assertEquals(1, $this->target->calls);
    }
}