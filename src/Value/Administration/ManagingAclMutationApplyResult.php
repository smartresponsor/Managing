<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Metadata-only result for applying a reviewed ACL mutation in owner-side services.
 *
 * This value must not contain secrets, raw policy internals, sessions, passwords,
 * or decrypted configuration values.
 */
final readonly class ManagingAclMutationApplyResult
{
    /** @param array<string, mixed> $safeContext */
    public function __construct(
        private string $requestKey,
        private string $status,
        private string $safeMessage,
        private bool $succeeded,
        private array $safeContext = [],
    ) {
    }

    /** @param array<string, mixed> $safeContext */
    public static function skipped(string $requestKey, string $safeMessage, array $safeContext = []): self
    {
        return new self($requestKey, 'skipped', $safeMessage, false, $safeContext);
    }

    /** @param array<string, mixed> $safeContext */
    public static function rejected(string $requestKey, string $safeMessage, array $safeContext = []): self
    {
        return new self($requestKey, 'rejected', $safeMessage, false, $safeContext);
    }

    /** @param array<string, mixed> $safeContext */
    public static function fromRollingResult(string $requestKey, bool $succeeded, string $status, string $safeMessage, array $safeContext = []): self
    {
        return new self($requestKey, $status, $safeMessage, $succeeded, $safeContext);
    }

    public function requestKey(): string
    {
        return $this->requestKey;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function safeMessage(): string
    {
        return $this->safeMessage;
    }

    public function succeeded(): bool
    {
        return $this->succeeded;
    }

    /** @return array<string, mixed> */
    public function safeContext(): array
    {
        return $this->safeContext;
    }

    /** @return array<string, mixed> */
    public function toSafeArray(): array
    {
        return [
            'request_key' => $this->requestKey,
            'status' => $this->status,
            'safe_message' => $this->safeMessage,
            'succeeded' => $this->succeeded,
            'safe_context' => $this->safeContext,
        ];
    }
}
