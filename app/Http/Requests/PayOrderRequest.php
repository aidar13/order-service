<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Payments\ProviderRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PayOrderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['idempotency_key' => $this->header('Idempotency-Key')]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'provider'        => ['required', 'string', Rule::in(app(ProviderRegistry::class)->names())],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function provider(): string
    {
        return (string)$this->validated('provider');
    }

    public function idempotencyKey(): ?string
    {
        $key = $this->validated('idempotency_key');

        return is_string($key) ? $key : null;
    }
}
