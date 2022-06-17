<?php

declare(strict_types=1);

namespace SentryMonologHandler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\Severity;
use Sentry\State\HubInterface;
use Sentry\State\Scope;

class MonologContextHandler extends AbstractProcessingHandler
{
    /**
     * Constructor.
     *
     * @param HubInterface $hub     The hub to which errors are reported
     * @param Level        $level   The minimum logging level at which this
     *                              handler will be triggered
     * @param bool         $bubble  Whether the messages that are handled can
     *                              bubble up the stack or not
     */
    public function __construct(private readonly HubInterface $hub, $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(LogRecord $record): void
    {
        $event = Event::createEvent();
        $event->setLevel(self::convertLogLevelToSeverity($record->level));
        $event->setMessage($record->message);
        $event->setLogger(sprintf('monolog.%s', $record->channel));

        $hint = new EventHint();

        if (isset($record->context['exception']) && $record->context['exception'] instanceof \Throwable) {
            $hint->exception = $record->context['exception'];
        }

        $this->hub->withScope(function (Scope $scope) use ($record, $event, $hint): void {
            $scope->setExtra('monolog.channel', $record['channel']);
            $scope->setExtra('monolog.level', $record['level_name']);

            if (isset($record->context[MonologFields::Tags->value])) {
                $scope->setTags($record->context[MonologFields::Tags->value]);
                unset($record->context[MonologFields::Tags->value]);
            }

            if (isset($record->context[MonologFields::Fingerprint->value])) {
                $scope->setFingerprint($record->context[MonologFields::Fingerprint->value]);
                unset($record->context[MonologFields::Fingerprint->value]);
            }

            if (isset($record->context)) {
                $scope->setContext('Context data', $record->context);
            }

            $this->hub->captureEvent($event, $hint);
        });
    }

    /**
     * Translates the Monolog level into the Sentry severity.
     */
    private static function convertLogLevelToSeverity(Level $level): Severity
    {
        return match ($level) {
            Level::Debug => Severity::debug(),
            Level::Warning => Severity::warning(),
            Level::Error => Severity::error(),
            Level::Critical, Level::Alert, Level::Emergency => Severity::fatal(),
            default => Severity::info(),
        };
    }
}
