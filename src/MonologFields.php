<?php

declare(strict_types=1);

namespace SentryMonologHandler;

/**
 * This class contains custom sentry monolog field keys that used in MonologContextHandler
 * for providing additional event information to Sentry events
 */
enum MonologFields: string
{
    case Tags = 'sentry_tags';
    case Fingerprint = 'fingerprint';
}
