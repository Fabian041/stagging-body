<?php

namespace App\Exceptions;

use RuntimeException;

class MachineAuthProtocolException extends RuntimeException
{
    private int $status;
    private string $reason;

    public function __construct(int $status, string $reason, string $message)
    {
        parent::__construct($message);

        $this->status = $status;
        $this->reason = $reason;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
