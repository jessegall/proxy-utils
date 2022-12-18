<?php

namespace Tests;

use Exception;
use JesseGall\Proxy\Forwarder\Strategies\Exceptions\ExecutionException;
use JesseGall\ProxyUtils\Handlers\ExceptionTransformer;
use Tests\TestClasses\TestException;

class ExceptionTransformerTest extends TestCase
{

    public function test__Given_closure__When_transform__Then_closure_invoked_with_execution_exception()
    {
        $exception = null;

        $transformer = new ExceptionTransformer([
            TestException::class => function (Exception $e) use (&$exception) {
                $exception = $e;

                return $e;
            }
        ]);

        $executionException = $this->generateException('methodThatThrowsException');

        $transformer->handle($executionException);

        $this->assertInstanceOf(ExecutionException::class, $exception);
    }

    public function test__When_add_transformation__Then_transformation_is_added()
    {
        $transformer = new ExceptionTransformer([]);

        $transformer->addTransformation(TestException::class, function (Exception $e) {
            return new AnotherException;
        });

        $transformations = $transformer->getTransformations();

        $this->assertArrayHasKey(TestException::class, $transformations);

        $this->assertIsCallable($transformations[TestException::class]);
    }

    public function test__Given_existing_transformation__When_add_transformation__Then_transformation_is_overwritten()
    {
        $transformer = new ExceptionTransformer([
            TestException::class => function (Exception $e) {
                return new AnotherException;
            }
        ]);

        $transformer->addTransformation(TestException::class, function (Exception $e) {
            return new YetAnotherException();
        });

        $transformations = $transformer->getTransformations();

        $this->assertArrayHasKey(TestException::class, $transformer->getTransformations());

        $this->assertIsCallable($transformations[TestException::class]);

        $this->assertSame(YetAnotherException::class, get_class($transformations[TestException::class](new TestException())));
    }

    public function test__Given_transformations__When_handle__Then_exception_is_transformed()
    {
        $transformer = new ExceptionTransformer([
            TestException::class => function (Exception $e) {
                return new AnotherException();
            }
        ]);

        $executionException = $this->generateException('methodThatThrowsException');

        $transformer->handle($executionException);

        $this->assertSame(AnotherException::class, get_class($executionException->getException()));
    }

    public function test__Given_transformations__When_handle_with_exception_not_in_transformations__Then_exception_is_not_transformed()
    {
        $transformer = new ExceptionTransformer([
            AnotherException::class => function (Exception $e) {
                return new TestException();
            }
        ]);

        $executionException = $this->generateException('methodThatThrowsException');

        $transformer->handle($executionException);

        $this->assertSame(TestException::class, get_class($executionException->getException()));
    }

    public function test__Given_no_transformations__When_handle__Then_exception_is_not_transformed()
    {
        $transformer = new ExceptionTransformer([]);

        $executionException = $this->generateException('methodThatThrowsException');

        $transformer->handle($executionException);

        $this->assertSame(TestException::class, get_class($executionException->getException()));
    }

}

class AnotherException extends Exception { }

class YetAnotherException extends Exception { }