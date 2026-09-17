<?php

namespace App\Exceptions;

use RuntimeException;

class FaceApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $apiStatus = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function isClientError(): bool
    {
        return $this->apiStatus !== null
            && $this->apiStatus >= 400
            && $this->apiStatus < 500;
    }
}
