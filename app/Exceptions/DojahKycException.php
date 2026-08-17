<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class DojahKycException extends RuntimeException
{
    public function __construct(
        string $message,
        protected ?int $statusCode = null,
        protected bool $retryable = false,
        ?Throwable $previous = null
    ) {

        parent::__construct(
            $message,
            $statusCode ?? 0,
            $previous
        );
    }


    public function statusCode(): ?int
    {
        return $this->statusCode;
    }


    public function retryable(): bool
    {
        return $this->retryable;
    }
}