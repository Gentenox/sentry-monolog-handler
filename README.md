# sentry-monolog-handler
Custom Sentry handler for Monolog for sending log context data to sentry events with additional event information
and it's focussed on ease-of-use and elegant syntax

## Installation
### Install via composer
You can install the package using the [Composer](https://getcomposer.org/) package manager. You can install it by running this command in your project root:

```sh
composer require gentenox/sentry-monolog-handler
```

### Add config for monolog handler to config/packages/monolog.yaml

```yaml
monolog:
  handlers:
    sentry:
      type: service
      id: monolog_context_handler
```

### Add new service to services.yaml

```yaml
monolog_context_handler:
  class: SentryMonologHandler\MonologContextHandler
  arguments:
    $hub: '@Sentry\State\HubInterface'
    $level: !php/const Monolog\Logger::ERROR
```

## Basic Usage

In examples below used monolog logger that implements **Psr\Log\LoggerInterface**

### Log error with context data

In this example sentry error handler receive logger context data end send it to Sentry as sentry **event context data**

```php
$logger->error('Cannot find existing order for user', [
    'user_id' => $user->getId()
]);
```

### Log with specific sentry properties

In this example sentry error handler receive logger context data and handle specific properties such as **tags** and **fingerprint**
These specific properties will not be added to logs and used only for providing additional event information to Sentry

Field | Type | Description
----- | ---- | -----------
`MonologFields::Tags` | array | Contains Sentry tags. Tags are key/value string pairs and used for filtering events
`MonologFields::Fingerprint` | array | Contains Sentry fingerprint. Events with the same fingerprint are grouped together into an issue

```php
$logger->error('Invalid postback received', [
    MonologFields::Tags->value => ['scope' => 'postback_validation']
    MonologFields::Fingerprint->value => ['postback_validation_fingerprint']
]);
```
