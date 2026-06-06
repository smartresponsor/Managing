<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * One host-activation issue found while checking field view profile storage.
 */
final readonly class ManageCrudFieldUserProfileStorageActivationIssue
{
    public function __construct(
        public string $severity,
        public string $code,
        public string $message,
    ) {
    }

    public static function error(string $code, string $message): self
    {
        return new self('error', $code, $message);
    }

    public static function warning(string $code, string $message): self
    {
        return new self('warning', $code, $message);
    }

    public static function info(string $code, string $message): self
    {
        return new self('info', $code, $message);
    }

    public function isError(): bool
    {
        return 'error' === $this->severity;
    }
}
