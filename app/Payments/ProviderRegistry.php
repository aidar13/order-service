<?php

declare(strict_types=1);

namespace App\Payments;

use App\Contracts\PaymentProvider;
use App\Exceptions\UnknownProvider;

final class ProviderRegistry
{
    /** @var array<string, PaymentProvider> */
    private array $providers = [];

    /** @param iterable<PaymentProvider> $providers */
    public function __construct(iterable $providers)
    {
        foreach ($providers as $provider) {
            $this->providers[$provider->name()] = $provider;
        }
    }

    public function get(string $name): PaymentProvider
    {
        return $this->providers[$name] ?? throw new UnknownProvider($name);
    }

    /** @return list<string> */
    public function names(): array
    {
        return array_keys($this->providers);
    }
}
