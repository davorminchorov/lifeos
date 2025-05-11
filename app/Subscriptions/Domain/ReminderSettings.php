<?php

namespace App\Subscriptions\Domain;

class ReminderSettings
{
    private function __construct(
        private readonly int $daysBefore,
        private readonly bool $enabled,
        private readonly string $method
    ) {
        if ($daysBefore < 1) {
            throw new \InvalidArgumentException('Days before payment must be at least 1');
        }
    }

    public static function create(int $daysBefore, bool $enabled, string $method): self
    {
        return new self($daysBefore, $enabled, $method);
    }

    public static function disabled(): self
    {
        return new self(7, false, 'email');
    }

    public function daysBefore(): int
    {
        return $this->daysBefore;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function withDaysBefore(int $daysBefore): self
    {
        return new self($daysBefore, $this->enabled, $this->method);
    }

    public function enable(): self
    {
        return new self($this->daysBefore, true, $this->method);
    }

    public function disable(): self
    {
        return new self($this->daysBefore, false, $this->method);
    }

    public function withMethod(string $method): self
    {
        return new self($this->daysBefore, $this->enabled, $method);
    }

    public function toArray(): array
    {
        return [
            'days_before' => $this->daysBefore,
            'enabled' => $this->enabled,
            'method' => $this->method
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['days_before'] ?? 7,
            $data['enabled'] ?? false,
            $data['method'] ?? 'email'
        );
    }
}
