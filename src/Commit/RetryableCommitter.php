<?php

declare(strict_types=1);

namespace Kafka\Consumer\Commit;

use RdKafka\Exception;

/**
 * Decorates a committer with retry logic
 *
 * It implements the exponential backoff algorithm
 */
class RetryableCommitter implements Committer
{
    private const RETRYABLE_ERRORS = [
        RD_KAFKA_RESP_ERR_REQUEST_TIMED_OUT
    ];

    private $committer;
    private $sleeper;
    private $maximumRetries;

    public function __construct(Committer $committer, Sleeper $sleeper, int $maximumRetries = 6)
    {
        $this->committer = $committer;
        $this->sleeper = $sleeper;
        $this->maximumRetries = $maximumRetries;
    }

    public function commitMessage(): void
    {
        $this->doCommit([$this->committer, 'commitMessage']);
    }

    public function commitDlq(): void
    {
        $this->doCommit([$this->committer, 'commitDlq']);
    }

    private function doCommit(callable $commitFunc, int $currentRetries = 0, int $timeToWait = 1)
    {
        try {
            $commitFunc();
        } catch (Exception $exception) {
            if (in_array($exception->getCode(), self::RETRYABLE_ERRORS) && $currentRetries < $this->maximumRetries) {
                $this->sleeper->sleep((int) ($timeToWait * 1e6));
                $this->doCommit($commitFunc, ++$currentRetries, $timeToWait * 2);
                return;
            }

            throw $exception;
        }
    }
}
