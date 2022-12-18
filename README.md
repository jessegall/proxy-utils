# ProxyUtils

ProxyUtils is a collection of utilities for the JesseGall/Proxy package, a library for creating proxies of classes and
objects to allow for custom behavior when their methods are called.
With ProxyUtils, developers can easily extend and customize the behavior of their proxies to fit their specific needs.

## Available utilities

Currently, there are two available utilities.
More utilities will be added in the future.

- RetryHandler
- ExceptionTransformer

## installation

```bash
composer require jessegall/proxy-utils
```

## RetryHandler

The `RetryHandler` class is a utility for retrying failed interactions.
It can be used to handle exceptions thrown during the execution of an interaction.

### Usage

To use the `RetryHandler`, create a new instance and pass it the maximum number of attempts allowed and an optional
closure that will be called before each attempt. If the closure returns `false`, the interaction will not be retried.
The handler can than be registered with a proxy to retry failed interactions.

```php
use JesseGall\Proxy\Proxy; 
use JesseGall\ProxyUtils\Handlers\RetryHandler;

$handler = new RetryHandler(5, function (Exception $exception, int $attempts) {
    // Explicitly return `false` if the interaction should not be retried.
    if (someCondition($exception)) {
        return false;
    }
});

// Register the handler with a proxy
$proxy = new Proxy($target);
$proxy->getForwarder()->registerExceptionHandler($handler);

// The proxy will now retry failed interactions
$proxy->doSomethingThatMightFail();
```

The `RetryHandler` will retry the interaction according to the specified criteria.
If the maximum number of attempts is reached or the `beforeRetry` closure returns `false`, the `RetryHandler` will not
retry the interaction and the exception will be thrown.

## ExceptionTransformer

The `ExceptionTransformer` class allows you to transform exceptions by providing a mapping of exception types to
transformations. This can be useful in situations where you want to catch certain exceptions and transform them into
different exceptions before rethrowing them.

### Usage

To use the `ExceptionTransformer` class, you will need to provide it with an array of transformations, where the keys
are class names of exception types and the values are closures that take an exception as an argument and return a
transformed exception. The handler can than be registered with a proxy to transform exceptions thrown during the
execution of an interaction.

Here is an example of how you might use the `ExceptionTransformer` class:

```php
use JesseGall\Proxy\Proxy;
use JesseGall\ProxyUtils\Handlers\ExceptionTransformer;

$transformer = new ExceptionTransformer([
    SomeException::class => function (Exception $e) {
        return new AnotherException;
    }
]);

// Register the handler with a proxy
$proxy = new Proxy($target);
$proxy->getForwarder()->registerExceptionHandler($transformer);

// Will throw an AnotherException instead of a SomeException
$proxy->someMethodThatThrowsSomeException();

```

You can also use the `addTransformation` method to add additional transformations or overwrite existing ones:

```php
$transformer->addTransformation(YetAnotherException::class, function (Exception $e) {
    return new SomeException;
});
```