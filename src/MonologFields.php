<?php
declare(strict_types=1);

namespace SentryMonologHandler;

/**
 * This class contains custom sentry monolog field keys that used in MonologContextHandler
 * for providing additional event information to Sentry events
 */
class MonologFields
{
    public const TAGS = 'sentry_tags';
    public const FINGERPRINT = 'fingerprint';
}
